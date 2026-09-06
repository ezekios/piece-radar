<?php

namespace Tests\Feature;

use App\Contracts\PlateProviderInterface;
use App\Data\VehicleIdentity;
use App\Data\VehicleLookupResult;
use App\Models\Part;
use App\Models\Scrapyard;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\ApiPlaqueProvider;
use App\Services\VehicleLookupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LicensePlateSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_api_plaque_provider_maps_successful_response(): void
    {
        $this->configureApiPlaque();

        Http::fake(fn (HttpRequest $request) => Http::response($this->successfulApiPayload(), 200));

        $result = app(ApiPlaqueProvider::class)->lookup('FH034DD');

        $this->assertTrue($result->success);
        $this->assertSame('identified', $result->status);
        $this->assertSame('FH034DD', $result->normalizedPlate);
        $this->assertSame('Renault', $result->vehicle?->brand);
        $this->assertSame('Clio IV', $result->vehicle?->model);
        $this->assertSame(2019, $result->vehicle?->year);
        $this->assertSame('1.5 dCi 90 (90 hp) [2012-2021]', $result->vehicle?->version);
        $this->assertSame('1.5 dCi 90 (90 hp)', $result->vehicle?->engine);
        $this->assertSame('Gazole', $result->vehicle?->fuel);
        $this->assertSame('K9K_638', $result->vehicle?->engineCode);
        $this->assertSame('57281', $result->vehicle?->kType);
        $this->assertSame('VF1TESTVIN', $result->vehicle?->vin);

        Http::assertSent(function (HttpRequest $request): bool {
            return $request->hasHeader('x-rapidapi-key')
                && $request->hasHeader('x-rapidapi-host', 'api-de-plaque-d-immatriculation-france.p.rapidapi.com')
                && $request->url() === 'https://api-de-plaque-d-immatriculation-france.p.rapidapi.com/?plaque=FH034DD';
        });
    }

    public function test_api_plaque_provider_converts_unknown_values_to_null(): void
    {
        $this->configureApiPlaque();

        Http::fake(fn () => Http::response([
            'error' => false,
            'code' => 200,
            'data' => [
                'marque' => 'RENAULT',
                'modele' => 'CLIO IV',
                'version' => 'INCONNU',
                'date_mise_en_circulation_us' => '',
                'energie' => 'INCONNU',
                'code_moteur' => null,
                'k_type' => 'INCONNU',
                'VIN' => 'INCONNU',
                'type_mine' => '',
            ],
        ], 200));

        $vehicle = app(ApiPlaqueProvider::class)->lookup('FH034DD')->vehicle;

        $this->assertSame('Renault', $vehicle?->brand);
        $this->assertSame('Clio IV', $vehicle?->model);
        $this->assertNull($vehicle?->year);
        $this->assertNull($vehicle?->version);
        $this->assertNull($vehicle?->engine);
        $this->assertNull($vehicle?->fuel);
        $this->assertNull($vehicle?->engineCode);
        $this->assertNull($vehicle?->kType);
        $this->assertNull($vehicle?->vin);
        $this->assertNull($vehicle?->typeMine);
    }

    public function test_api_plaque_provider_handles_expected_error_statuses(): void
    {
        $this->configureApiPlaque();

        $cases = [
            'AA111AA' => [400, 'invalid_format', VehicleLookupResult::invalidFormatMessage()],
            'BB222BB' => [401, 'provider_unavailable', VehicleLookupResult::genericUnavailableMessage()],
            'CC333CC' => [404, 'not_found', VehicleLookupResult::notFoundMessage()],
            'DD444DD' => [429, 'provider_unavailable', VehicleLookupResult::genericUnavailableMessage()],
            'EE555EE' => [500, 'provider_unavailable', VehicleLookupResult::genericUnavailableMessage()],
        ];

        Http::fake(function (HttpRequest $request) use ($cases) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);
            $plate = (string) ($query['plaque'] ?? '');

            return Http::response(['error' => true], $cases[$plate][0] ?? 500);
        });

        foreach ($cases as $plate => [, $expectedStatus, $expectedMessage]) {
            $result = app(ApiPlaqueProvider::class)->lookup($plate);

            $this->assertFalse($result->success);
            $this->assertSame($expectedStatus, $result->status);
            $this->assertSame($expectedMessage, $result->message);
        }
    }

    public function test_api_plaque_provider_handles_timeout_and_invalid_payload(): void
    {
        $this->configureApiPlaque();

        Http::fake(fn () => throw new ConnectionException('Connection timed out.'));

        $timeoutResult = app(ApiPlaqueProvider::class)->lookup('AB123CD');

        $this->assertFalse($timeoutResult->success);
        $this->assertSame('provider_unavailable', $timeoutResult->status);

        Http::fake(fn () => Http::response(['error' => false, 'code' => 200], 200));

        $invalidPayloadResult = app(ApiPlaqueProvider::class)->lookup('AB123CD');

        $this->assertFalse($invalidPayloadResult->success);
        $this->assertSame('provider_unavailable', $invalidPayloadResult->status);
    }

    public function test_vehicle_lookup_service_caches_success_for_same_normalized_plate(): void
    {
        $provider = new class implements PlateProviderInterface
        {
            public int $calls = 0;

            public function lookup(string $normalizedPlate): VehicleLookupResult
            {
                $this->calls++;

                return VehicleLookupResult::success(
                    $normalizedPlate,
                    new VehicleIdentity(brand: 'Renault', model: 'Clio IV', year: 2019),
                );
            }
        };

        $service = new VehicleLookupService($provider);

        $first = $service->lookup('AB-123-CD');
        $second = $service->lookup('ab 123 cd');
        $third = $service->lookup('AB123CD');

        $this->assertTrue($first->success);
        $this->assertTrue($second->success);
        $this->assertTrue($third->success);
        $this->assertSame('AB123CD', $first->normalizedPlate);
        $this->assertSame(1, $provider->calls);
    }

    public function test_vehicle_lookup_service_caches_not_found_but_not_unavailable_errors(): void
    {
        $provider = new class implements PlateProviderInterface
        {
            public int $calls = 0;

            public function lookup(string $normalizedPlate): VehicleLookupResult
            {
                $this->calls++;

                if ($normalizedPlate === 'AA111AA') {
                    return VehicleLookupResult::failure(
                        'not_found',
                        $normalizedPlate,
                        VehicleLookupResult::notFoundMessage(),
                    );
                }

                return VehicleLookupResult::failure(
                    'provider_unavailable',
                    $normalizedPlate,
                    VehicleLookupResult::genericUnavailableMessage(),
                );
            }
        };

        $service = new VehicleLookupService($provider);

        $service->lookup('AA-111-AA');
        $service->lookup('AA111AA');

        $this->assertSame(1, $provider->calls);

        $service->lookup('BB-222-BB');
        $service->lookup('BB222BB');

        $this->assertSame(3, $provider->calls);
    }

    public function test_parts_index_without_license_plate_does_not_call_http_and_keeps_manual_search(): void
    {
        $scrapyard = $this->createScrapyard();
        $this->createPart($this->createVehicle($scrapyard, brand: 'Renault', model: 'Clio'), [
            'name' => 'Alternateur manuel',
        ]);

        $this->get(route('client.parts.index', ['q' => 'Alternateur']))
            ->assertOk()
            ->assertSee('Alternateur manuel')
            ->assertDontSee('Véhicule identifié');
    }

    public function test_parts_index_with_recognized_plate_displays_identity_and_assists_results(): void
    {
        $this->configureApiPlaque();
        Http::fake(fn () => Http::response($this->successfulApiPayload(), 200));

        $scrapyard = $this->createScrapyard();
        $this->createPart($this->createVehicle($scrapyard, brand: 'Renault', model: 'Clio'), [
            'name' => 'Alternateur Clio',
        ]);
        $this->createPart($this->createVehicle($scrapyard, brand: 'Peugeot', model: '208'), [
            'name' => 'Alternateur 208',
        ]);

        $this->get(route('client.parts.index', ['license_plate' => 'FH-034-DD']))
            ->assertOk()
            ->assertSee('Véhicule identifié')
            ->assertSee('Renault Clio IV')
            ->assertSee('2019')
            ->assertSee('1.5 dCi 90')
            ->assertSee('Gazole')
            ->assertSee('Alternateur Clio')
            ->assertDontSee('Alternateur 208')
            ->assertDontSee('fake-rapidapi-key')
            ->assertDontSee('VF1TESTVIN')
            ->assertDontSee('57281');
    }

    public function test_parts_index_with_unknown_plate_keeps_manual_fallback(): void
    {
        $this->configureApiPlaque();
        Http::fake(fn () => Http::response(['error' => true], 404));

        $scrapyard = $this->createScrapyard();
        $this->createPart($this->createVehicle($scrapyard, brand: 'Peugeot', model: '208'), [
            'name' => 'Phare fallback',
        ]);

        $this->get(route('client.parts.index', [
            'license_plate' => 'ZZ-999-ZZ',
            'q' => 'Phare',
        ]))
            ->assertOk()
            ->assertSee(VehicleLookupResult::notFoundMessage())
            ->assertSee('Phare fallback')
            ->assertDontSee('Véhicule identifié');
    }

    public function test_parts_index_with_provider_unavailable_keeps_manual_fallback(): void
    {
        $this->configureApiPlaque();
        Http::fake(fn () => Http::response(['error' => true], 429));

        $scrapyard = $this->createScrapyard();
        $this->createPart($this->createVehicle($scrapyard, brand: 'Peugeot', model: '208'), [
            'name' => 'Rétroviseur fallback',
        ]);

        $this->get(route('client.parts.index', [
            'license_plate' => 'AB-123-CD',
            'q' => 'Rétroviseur',
        ]))
            ->assertOk()
            ->assertSee(VehicleLookupResult::genericUnavailableMessage())
            ->assertSee('Rétroviseur fallback');
    }

    public function test_parts_index_with_server_error_or_timeout_keeps_manual_fallback(): void
    {
        $this->configureApiPlaque();
        $scrapyard = $this->createScrapyard();
        $this->createPart($this->createVehicle($scrapyard, brand: 'Peugeot', model: '208'), [
            'name' => 'Capot fallback',
        ]);

        Http::fake(fn () => Http::response(['error' => true], 500));

        $this->get(route('client.parts.index', [
            'license_plate' => 'AB-123-CD',
            'q' => 'Capot',
        ]))
            ->assertOk()
            ->assertSee(VehicleLookupResult::genericUnavailableMessage())
            ->assertSee('Capot fallback');

        Cache::flush();
        Http::fake(fn () => throw new ConnectionException('Connection timed out.'));

        $this->get(route('client.parts.index', [
            'license_plate' => 'CD-456-EF',
            'q' => 'Capot',
        ]))
            ->assertOk()
            ->assertSee(VehicleLookupResult::genericUnavailableMessage())
            ->assertSee('Capot fallback');
    }

    /**
     * @return array<string, mixed>
     */
    private function successfulApiPayload(): array
    {
        return [
            'error' => false,
            'code' => 200,
            'message' => 'Succès',
            'query' => 'FH-034-DD',
            'country' => 'FR',
            'data' => [
                'immat' => 'FH034DD',
                'VIN' => 'VF1TESTVIN',
                'marque' => 'RENAULT',
                'modele' => 'CLIO IV',
                'version' => '1.5 dCi 90 (90 hp) [2012-2021]',
                'date_mise_en_circulation_us' => '2019-06-20',
                'energie' => 'GAZOLE',
                'code_moteur' => 'K9K_638',
                'puissance_chevaux' => '90',
                'k_type' => '57281',
                'finition' => 'INCONNU',
            ],
        ];
    }

    private function configureApiPlaque(): void
    {
        config([
            'services.api_plaque.key' => 'fake-rapidapi-key',
            'services.api_plaque.host' => 'api-de-plaque-d-immatriculation-france.p.rapidapi.com',
            'services.api_plaque.timeout' => 8,
        ]);
    }

    private function createScrapyard(): Scrapyard
    {
        $user = User::factory()->create([
            'name' => 'Casse Test',
            'email' => 'casse-' . uniqid() . '@example.com',
            'phone' => '0596000000',
            'password' => Hash::make('password'),
        ]);
        $user->forceFill(['role' => 'scrapyard'])->save();

        return Scrapyard::query()->create([
            'user_id' => $user->id,
            'name' => 'Casse Test',
            'slug' => 'casse-test-' . uniqid(),
            'city' => 'Fort-de-France',
            'is_active' => true,
        ]);
    }

    private function createVehicle(Scrapyard $scrapyard, string $brand, string $model): Vehicle
    {
        return Vehicle::query()->create([
            'scrapyard_id' => $scrapyard->id,
            'brand' => $brand,
            'model' => $model,
            'year' => 2019,
            'engine' => '1.5 dCi',
            'fuel' => 'Gazole',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createPart(Vehicle $vehicle, array $attributes = []): Part
    {
        return Part::query()->create([
            'vehicle_id' => $vehicle->id,
            'name' => $attributes['name'] ?? 'Alternateur',
            'category' => 'Moteur',
            'condition' => 'used_good',
            'status' => 'available',
            'price' => 120,
            'is_published' => true,
        ]);
    }
}
