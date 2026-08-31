<?php

namespace Tests\Feature;

use App\Models\Part;
use App\Models\PartHoldRequest;
use App\Models\Scrapyard;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ScrapyardOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_scrapyard_sees_only_own_vehicles(): void
    {
        [$userA, $scrapyardA] = $this->createScrapyardAccount('Casse A');
        [, $scrapyardB] = $this->createScrapyardAccount('Casse B');
        $vehicleA = $this->createVehicle($scrapyardA, 'Renault Alpha');
        $vehicleB = $this->createVehicle($scrapyardB, 'Peugeot Bravo');

        $response = $this->actingAs($userA)->get(route('scrapyard.vehicles.index'));

        $response
            ->assertOk()
            ->assertSee('Renault Alpha')
            ->assertDontSee('Peugeot Bravo')
            ->assertViewHas('vehicles', function ($vehicles) use ($vehicleA, $vehicleB) {
                return $vehicles->contains('id', $vehicleA->id)
                    && ! $vehicles->contains('id', $vehicleB->id);
            });
    }

    public function test_scrapyard_can_view_own_vehicle(): void
    {
        [$userA, $scrapyardA] = $this->createScrapyardAccount('Casse A');
        $vehicleA = $this->createVehicle($scrapyardA, 'Renault Alpha');

        $this->actingAs($userA)
            ->get(route('scrapyard.vehicles.show', $vehicleA))
            ->assertOk()
            ->assertSee('Renault Alpha');
    }

    public function test_scrapyard_cannot_view_edit_or_update_other_vehicle(): void
    {
        [$userA] = $this->createScrapyardAccount('Casse A');
        [, $scrapyardB] = $this->createScrapyardAccount('Casse B');
        $vehicleB = $this->createVehicle($scrapyardB, 'Peugeot Bravo');

        $this->actingAs($userA)
            ->get(route('scrapyard.vehicles.show', $vehicleB))
            ->assertNotFound();

        $this->actingAs($userA)
            ->get(route('scrapyard.vehicles.edit', $vehicleB))
            ->assertNotFound();

        $this->actingAs($userA)
            ->post(route('scrapyard.vehicles.update', $vehicleB), [
                'brand' => 'Modified',
                'model' => 'Blocked',
                'year' => 2020,
            ])
            ->assertNotFound();

        $this->assertSame('Peugeot Bravo', $vehicleB->fresh()->brand);
    }

    public function test_vehicle_creation_uses_authenticated_scrapyard(): void
    {
        [$userA, $scrapyardA] = $this->createScrapyardAccount('Casse A');
        [, $scrapyardB] = $this->createScrapyardAccount('Casse B');

        $this->actingAs($userA)
            ->post(route('scrapyard.vehicles.store'), [
                'scrapyard_id' => $scrapyardB->id,
                'brand' => 'Citroen',
                'model' => 'C3',
                'year' => 2021,
            ])
            ->assertRedirect();

        $vehicle = Vehicle::query()->where('brand', 'Citroen')->firstOrFail();

        $this->assertSame($scrapyardA->id, $vehicle->scrapyard_id);
    }

    public function test_scrapyard_sees_only_own_parts(): void
    {
        [$userA, $scrapyardA] = $this->createScrapyardAccount('Casse A');
        [, $scrapyardB] = $this->createScrapyardAccount('Casse B');
        $partA = $this->createPart($this->createVehicle($scrapyardA, 'Renault Alpha'), 'Optique Alpha');
        $partB = $this->createPart($this->createVehicle($scrapyardB, 'Peugeot Bravo'), 'Optique Bravo');

        $response = $this->actingAs($userA)->get(route('scrapyard.parts.index'));

        $response
            ->assertOk()
            ->assertSee('Optique Alpha')
            ->assertDontSee('Optique Bravo')
            ->assertViewHas('parts', function ($parts) use ($partA, $partB) {
                return $parts->contains('id', $partA->id)
                    && ! $parts->contains('id', $partB->id);
            });
    }

    public function test_scrapyard_cannot_access_or_modify_other_part(): void
    {
        [$userA] = $this->createScrapyardAccount('Casse A');
        [, $scrapyardB] = $this->createScrapyardAccount('Casse B');
        $partB = $this->createPart($this->createVehicle($scrapyardB, 'Peugeot Bravo'), 'Optique Bravo', [
            'status' => 'preparing',
            'is_published' => false,
        ]);

        $this->actingAs($userA)
            ->get(route('scrapyard.parts.show', $partB))
            ->assertNotFound();

        $this->actingAs($userA)
            ->get(route('scrapyard.parts.preparation.edit', $partB))
            ->assertNotFound();

        $this->actingAs($userA)
            ->post(route('scrapyard.parts.publish', $partB))
            ->assertNotFound();

        $this->actingAs($userA)
            ->post(route('scrapyard.parts.unpublish', $partB))
            ->assertNotFound();

        $this->actingAs($userA)
            ->post(route('scrapyard.parts.updateStatus', $partB), [
                'status' => 'available',
            ])
            ->assertNotFound();

        $this->actingAs($userA)
            ->post(route('scrapyard.parts.preparation.update', $partB), [
                'name' => 'Modified',
                'status' => 'available',
            ])
            ->assertNotFound();

        $this->assertSame('Optique Bravo', $partB->fresh()->name);
        $this->assertSame('preparing', $partB->fresh()->status);
        $this->assertFalse($partB->fresh()->is_published);
    }

    public function test_scrapyard_cannot_create_part_from_other_vehicle(): void
    {
        [$userA] = $this->createScrapyardAccount('Casse A');
        [, $scrapyardB] = $this->createScrapyardAccount('Casse B');
        $vehicleB = $this->createVehicle($scrapyardB, 'Peugeot Bravo');

        $this->actingAs($userA)
            ->get(route('scrapyard.vehicles.parts.create', $vehicleB))
            ->assertNotFound();

        $this->actingAs($userA)
            ->post(route('scrapyard.vehicles.parts.store', $vehicleB), [
                'name' => 'Pièce interdite',
                'status' => 'available',
            ])
            ->assertNotFound();

        $this->assertFalse(Part::query()->where('name', 'Pièce interdite')->exists());
    }

    public function test_scrapyard_sees_only_own_requests(): void
    {
        [$userA, $scrapyardA] = $this->createScrapyardAccount('Casse A');
        [, $scrapyardB] = $this->createScrapyardAccount('Casse B');
        $requestA = $this->createHoldRequest($this->createPart($this->createVehicle($scrapyardA, 'Renault Alpha'), 'Optique Alpha'));
        $requestB = $this->createHoldRequest($this->createPart($this->createVehicle($scrapyardB, 'Peugeot Bravo'), 'Optique Bravo'));

        $response = $this->actingAs($userA)->get(route('scrapyard.requests.index'));

        $response
            ->assertOk()
            ->assertSee('Optique Alpha')
            ->assertDontSee('Optique Bravo')
            ->assertViewHas('requests', function ($requests) use ($requestA, $requestB) {
                return $requests->contains('id', $requestA->id)
                    && ! $requests->contains('id', $requestB->id);
            });
    }

    public function test_scrapyard_cannot_access_or_modify_other_request(): void
    {
        [$userA] = $this->createScrapyardAccount('Casse A');
        [, $scrapyardB] = $this->createScrapyardAccount('Casse B');
        $partB = $this->createPart($this->createVehicle($scrapyardB, 'Peugeot Bravo'), 'Optique Bravo');
        $requestB = $this->createHoldRequest($partB);

        $this->actingAs($userA)
            ->get(route('scrapyard.requests.show', $requestB))
            ->assertNotFound();

        $this->actingAs($userA)
            ->get(route('scrapyard.requests.accept.confirm', $requestB))
            ->assertNotFound();

        $this->actingAs($userA)
            ->post(route('scrapyard.requests.accept', $requestB))
            ->assertNotFound();

        $this->actingAs($userA)
            ->post(route('scrapyard.requests.refuse', $requestB))
            ->assertNotFound();

        $requestB->update(['status' => 'accepted']);
        $partB->update(['status' => 'reserved']);

        $this->actingAs($userA)
            ->post(route('scrapyard.requests.complete', $requestB))
            ->assertNotFound();

        $this->actingAs($userA)
            ->post(route('scrapyard.requests.cancel', $requestB))
            ->assertNotFound();

        $this->assertSame('accepted', $requestB->fresh()->status);
        $this->assertSame('reserved', $partB->fresh()->status);
    }

    public function test_dashboard_counts_only_authenticated_scrapyard_resources(): void
    {
        [$userA, $scrapyardA] = $this->createScrapyardAccount('Casse A');
        [, $scrapyardB] = $this->createScrapyardAccount('Casse B');
        $vehicleA = $this->createVehicle($scrapyardA, 'Renault Alpha');
        $vehicleB = $this->createVehicle($scrapyardB, 'Peugeot Bravo');
        $this->createPart($vehicleA, 'Pièce A disponible', ['status' => 'available', 'is_published' => true]);
        $this->createPart($vehicleA, 'Pièce A réservée', ['status' => 'reserved', 'is_published' => false]);
        $this->createPart($vehicleB, 'Pièce B disponible', ['status' => 'available', 'is_published' => true]);
        $this->createHoldRequest($this->createPart($vehicleA, 'Demande A pending'), 'pending');
        $this->createHoldRequest($this->createPart($vehicleA, 'Demande A accepted'), 'accepted');
        $this->createHoldRequest($this->createPart($vehicleA, 'Demande A refused'), 'refused');
        $this->createHoldRequest($this->createPart($vehicleA, 'Demande A cancelled'), 'cancelled');
        $this->createHoldRequest($this->createPart($vehicleA, 'Demande A completed'), 'completed');
        $this->createHoldRequest($this->createPart($vehicleA, 'Demande A expired'), 'expired');
        $this->createHoldRequest($this->createPart($vehicleB, 'Demande B pending'), 'pending');

        $this->actingAs($userA)
            ->get(route('scrapyard.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', function (array $stats) {
                return $stats['vehicles_total'] === 1
                    && $stats['parts_total'] === 8
                    && $stats['requests_total'] === 6
                    && $stats['pending_requests'] === 1
                    && $stats['accepted_requests'] === 1
                    && $stats['refused_requests'] === 1
                    && $stats['cancelled_requests'] === 1
                    && $stats['completed_requests'] === 1
                    && $stats['expired_requests'] === 1;
            })
            ->assertSee('Casse A')
            ->assertDontSee('Casse B');
    }

    /**
     * @return array{0: User, 1: Scrapyard}
     */
    private function createScrapyardAccount(string $name): array
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '-', $name)) . '-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->forceFill([
            'role' => 'scrapyard',
        ])->save();

        $scrapyard = Scrapyard::query()->create([
            'user_id' => $user->id,
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)) . '-' . uniqid(),
            'city' => 'Fort-de-France',
            'is_active' => true,
        ]);

        return [$user, $scrapyard];
    }

    private function createVehicle(Scrapyard $scrapyard, string $brand): Vehicle
    {
        return Vehicle::query()->create([
            'scrapyard_id' => $scrapyard->id,
            'brand' => $brand,
            'model' => 'Model ' . uniqid(),
            'year' => 2018,
            'engine' => '1.5 dCi',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createPart(Vehicle $vehicle, string $name, array $attributes = []): Part
    {
        return Part::query()->create([
            'vehicle_id' => $vehicle->id,
            'name' => $name,
            'category' => 'Optique',
            'condition' => 'used_good',
            'status' => $attributes['status'] ?? 'available',
            'price' => 85,
            'is_published' => $attributes['is_published'] ?? true,
        ]);
    }

    private function createHoldRequest(Part $part, string $status = 'pending'): PartHoldRequest
    {
        $client = User::factory()->create([
            'name' => 'Client ' . uniqid(),
            'email' => 'client-' . uniqid() . '@example.com',
            'phone' => '0696000001',
        ]);

        return PartHoldRequest::query()->create([
            'user_id' => $client->id,
            'part_id' => $part->id,
            'status' => $status,
            'customer_message' => 'Je souhaite réserver cette pièce.',
        ]);
    }
}
