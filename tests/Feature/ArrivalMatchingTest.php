<?php

namespace Tests\Feature;

use App\Models\Part;
use App\Models\SavedPartSearch;
use App\Models\Scrapyard;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ArrivalMatchingTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_client_can_save_search_from_empty_results(): void
    {
        $client = $this->createClientUser();

        $this->actingAs($client)
            ->get(route('client.parts.index', [
                'q' => 'Démarreur',
                'brand' => 'Renault',
                'model' => 'Clio',
            ]))
            ->assertOk()
            ->assertSee('Aucune pièce ne correspond à votre recherche.')
            ->assertSee('Enregistrer ma recherche')
            ->assertSee('value="Démarreur"', false)
            ->assertSee('value="Renault"', false)
            ->assertSee('value="Clio"', false);

        $this->actingAs($client)
            ->post(route('client.saved-searches.store'), [
                'part_name' => 'Démarreur',
                'part_category' => 'Électricité',
                'vehicle_brand' => 'Renault',
                'vehicle_model' => 'Clio',
                'vehicle_year' => 2018,
            ])
            ->assertRedirect(route('client.saved-searches.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('saved_part_searches', [
            'user_id' => $client->id,
            'part_name' => 'Démarreur',
            'part_category' => 'Électricité',
            'vehicle_brand' => 'Renault',
            'vehicle_model' => 'Clio',
            'vehicle_year' => 2018,
            'status' => 'active',
            'matched_part_id' => null,
        ]);
    }

    public function test_guest_cannot_create_persistent_search(): void
    {
        $this->post(route('client.saved-searches.store'), [
            'part_name' => 'Alternateur',
            'vehicle_brand' => 'Renault',
            'vehicle_model' => 'Clio',
        ])
            ->assertRedirect(route('login'));

        $this->assertSame(0, SavedPartSearch::query()->count());
    }

    public function test_scrapyard_user_cannot_use_client_saved_search_routes(): void
    {
        [$scrapyardUser] = $this->createScrapyardAccount('Casse Test');

        $this->actingAs($scrapyardUser)
            ->get(route('client.saved-searches.index'))
            ->assertForbidden();

        $this->actingAs($scrapyardUser)
            ->post(route('client.saved-searches.store'), [
                'part_name' => 'Alternateur',
                'vehicle_brand' => 'Renault',
                'vehicle_model' => 'Clio',
            ])
            ->assertForbidden();

        $this->assertSame(0, SavedPartSearch::query()->count());
    }

    public function test_client_sees_only_own_saved_searches(): void
    {
        $clientA = $this->createClientUser(['name' => 'Client A']);
        $clientB = $this->createClientUser(['name' => 'Client B']);
        $this->createSavedSearch($clientA, ['part_name' => 'Alternateur Client A']);
        $this->createSavedSearch($clientB, ['part_name' => 'Alternateur Client B']);

        $this->actingAs($clientA)
            ->get(route('client.saved-searches.index'))
            ->assertOk()
            ->assertSee('Alternateur Client A')
            ->assertDontSee('Alternateur Client B');
    }

    public function test_new_saved_search_is_active_and_cannot_choose_match_fields(): void
    {
        $client = $this->createClientUser();
        [, $scrapyard] = $this->createScrapyardAccount('Casse Match');
        $part = $this->createPart($this->createVehicle($scrapyard));

        $this->actingAs($client)
            ->post(route('client.saved-searches.store'), [
                'part_name' => 'Alternateur',
                'vehicle_brand' => 'Renault',
                'vehicle_model' => 'Clio',
                'status' => 'matched',
                'matched_part_id' => $part->id,
                'user_id' => 999,
            ])
            ->assertRedirect(route('client.saved-searches.index'));

        $savedSearch = SavedPartSearch::query()->firstOrFail();

        $this->assertSame($client->id, $savedSearch->user_id);
        $this->assertSame('active', $savedSearch->status);
        $this->assertNull($savedSearch->matched_part_id);
        $this->assertNull($savedSearch->matched_at);
    }

    public function test_publishing_compatible_part_matches_active_search(): void
    {
        $client = $this->createClientUser();
        [$scrapyardUser, $scrapyard] = $this->createScrapyardAccount('Casse Compatible');
        $savedSearch = $this->createSavedSearch($client, [
            'vehicle_brand' => ' renault ',
            'vehicle_model' => 'CLIO',
            'vehicle_year' => 2018,
            'part_name' => 'alternateur',
            'part_category' => 'moteur',
        ]);
        $part = $this->createPart(
            $this->createVehicle($scrapyard, ['brand' => 'Renault', 'model' => 'Clio', 'year' => 2018]),
            [
                'name' => 'Alternateur complet',
                'category' => 'Moteur',
                'status' => 'preparing',
                'is_published' => false,
            ]
        );

        $this->actingAs($scrapyardUser)
            ->post(route('scrapyard.parts.publish', $part))
            ->assertRedirect(route('scrapyard.parts.show', $part));

        $savedSearch->refresh();
        $part->refresh();

        $this->assertSame('matched', $savedSearch->status);
        $this->assertSame($part->id, $savedSearch->matched_part_id);
        $this->assertNotNull($savedSearch->matched_at);
        $this->assertSame('available', $part->status);
        $this->assertTrue($part->is_published);
    }

    public function test_part_must_be_available_and_published_to_trigger_matching(): void
    {
        $client = $this->createClientUser();
        [$scrapyardUser, $scrapyard] = $this->createScrapyardAccount('Casse Publication');
        $savedSearch = $this->createSavedSearch($client, [
            'vehicle_brand' => 'Renault',
            'vehicle_model' => 'Clio',
            'part_name' => 'Phare',
        ]);
        $vehicle = $this->createVehicle($scrapyard, ['brand' => 'Renault', 'model' => 'Clio']);

        $this->actingAs($scrapyardUser)
            ->post(route('scrapyard.vehicles.parts.store', $vehicle), [
                'name' => 'Phare avant droit',
                'category' => 'Optique',
                'condition' => 'used_good',
                'status' => 'available',
                'price' => 75,
            ])
            ->assertRedirect(route('scrapyard.vehicles.show', $vehicle));

        $createdPart = Part::query()->where('name', 'Phare avant droit')->firstOrFail();

        $this->assertSame('available', $createdPart->status);
        $this->assertFalse($createdPart->is_published);
        $this->assertSame('active', $savedSearch->fresh()->status);
        $this->assertNull($savedSearch->fresh()->matched_part_id);

        $this->actingAs($scrapyardUser)
            ->post(route('scrapyard.parts.publish', $createdPart))
            ->assertRedirect(route('scrapyard.parts.show', $createdPart));

        $this->assertSame('matched', $savedSearch->fresh()->status);
        $this->assertSame($createdPart->id, $savedSearch->fresh()->matched_part_id);
    }

    public function test_incompatible_part_does_not_trigger_matching(): void
    {
        $client = $this->createClientUser();
        [$scrapyardUser, $scrapyard] = $this->createScrapyardAccount('Casse Incompatible');
        $savedSearch = $this->createSavedSearch($client, [
            'vehicle_brand' => 'Renault',
            'vehicle_model' => 'Clio',
            'vehicle_year' => 2018,
            'part_name' => 'Alternateur',
        ]);
        $part = $this->createPart(
            $this->createVehicle($scrapyard, ['brand' => 'Peugeot', 'model' => '208', 'year' => 2018]),
            [
                'name' => 'Alternateur complet',
                'status' => 'preparing',
                'is_published' => false,
            ]
        );

        $this->actingAs($scrapyardUser)
            ->post(route('scrapyard.parts.publish', $part))
            ->assertRedirect(route('scrapyard.parts.show', $part));

        $this->assertSame('active', $savedSearch->fresh()->status);
        $this->assertNull($savedSearch->fresh()->matched_part_id);
    }

    public function test_update_status_to_available_triggers_matching(): void
    {
        $client = $this->createClientUser();
        [$scrapyardUser, $scrapyard] = $this->createScrapyardAccount('Casse Statut');
        $savedSearch = $this->createSavedSearch($client, [
            'vehicle_brand' => 'Citroen',
            'vehicle_model' => 'C3',
            'part_name' => 'Capot',
        ]);
        $part = $this->createPart(
            $this->createVehicle($scrapyard, ['brand' => 'Citroen', 'model' => 'C3']),
            [
                'name' => 'Capot avant',
                'status' => 'preparing',
                'is_published' => false,
            ]
        );

        $this->actingAs($scrapyardUser)
            ->post(route('scrapyard.parts.updateStatus', $part), [
                'status' => 'available',
            ])
            ->assertRedirect(route('scrapyard.parts.show', $part));

        $this->assertSame('matched', $savedSearch->fresh()->status);
        $this->assertSame($part->id, $savedSearch->fresh()->matched_part_id);
        $this->assertSame('available', $part->fresh()->status);
        $this->assertTrue($part->fresh()->is_published);
    }

    public function test_matched_search_allows_client_to_open_public_part(): void
    {
        $client = $this->createClientUser();
        [$scrapyardUser, $scrapyard] = $this->createScrapyardAccount('Casse Public');
        $savedSearch = $this->createSavedSearch($client, [
            'vehicle_brand' => 'Toyota',
            'vehicle_model' => 'Yaris',
            'part_name' => 'Rétroviseur',
        ]);
        $part = $this->createPart(
            $this->createVehicle($scrapyard, ['brand' => 'Toyota', 'model' => 'Yaris']),
            [
                'name' => 'Rétroviseur droit',
                'status' => 'preparing',
                'is_published' => false,
            ]
        );

        $this->actingAs($scrapyardUser)
            ->post(route('scrapyard.parts.publish', $part));

        $this->actingAs($client)
            ->get(route('client.saved-searches.index'))
            ->assertOk()
            ->assertSee('Correspondance trouvée')
            ->assertSee('Voir la pièce')
            ->assertSee(route('pieces.show', $part), false);

        $this->get(route('pieces.show', $part))
            ->assertOk()
            ->assertSee('Rétroviseur droit');

        $this->assertSame($part->id, $savedSearch->fresh()->matched_part_id);
    }

    public function test_existing_public_part_search_still_returns_only_available_published_parts(): void
    {
        [, $scrapyard] = $this->createScrapyardAccount('Casse Recherche');
        $vehicle = $this->createVehicle($scrapyard, ['brand' => 'Renault', 'model' => 'Clio']);
        $visiblePart = $this->createPart($vehicle, [
            'name' => 'Alternateur visible',
            'status' => 'available',
            'is_published' => true,
        ]);
        $this->createPart($vehicle, [
            'name' => 'Alternateur réservé',
            'status' => 'reserved',
            'is_published' => true,
        ]);
        $this->createPart($vehicle, [
            'name' => 'Alternateur caché',
            'status' => 'available',
            'is_published' => false,
        ]);

        $this->get(route('client.parts.index', ['q' => 'Alternateur']))
            ->assertOk()
            ->assertSee($visiblePart->name)
            ->assertDontSee('Alternateur réservé')
            ->assertDontSee('Alternateur caché')
            ->assertDontSee('Aucune pièce ne correspond à votre recherche.');
    }

    private function createClientUser(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ], $attributes));

        $user->forceFill(['role' => 'client'])->save();

        return $user;
    }

    /**
     * @param  array<string, mixed>  $userAttributes
     * @return array{0: User, 1: Scrapyard}
     */
    private function createScrapyardAccount(string $name, array $userAttributes = []): array
    {
        $user = User::factory()->create(array_merge([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '-', $name)) . '-' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ], $userAttributes));

        $user->forceFill(['role' => 'scrapyard'])->save();

        $scrapyard = Scrapyard::query()->create([
            'user_id' => $user->id,
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)) . '-' . uniqid(),
            'city' => 'Fort-de-France',
            'is_active' => true,
        ]);

        return [$user, $scrapyard];
    }

    private function createVehicle(Scrapyard $scrapyard, array $attributes = []): Vehicle
    {
        return Vehicle::query()->create([
            'scrapyard_id' => $scrapyard->id,
            'brand' => $attributes['brand'] ?? 'Renault',
            'model' => $attributes['model'] ?? 'Clio',
            'year' => $attributes['year'] ?? 2018,
            'engine' => $attributes['engine'] ?? '1.5 dCi',
            'fuel' => $attributes['fuel'] ?? 'Gazole',
        ]);
    }

    private function createPart(Vehicle $vehicle, array $attributes = []): Part
    {
        return Part::query()->create([
            'vehicle_id' => $vehicle->id,
            'name' => $attributes['name'] ?? 'Alternateur',
            'category' => $attributes['category'] ?? 'Moteur',
            'condition' => $attributes['condition'] ?? 'used_good',
            'status' => $attributes['status'] ?? 'available',
            'price' => $attributes['price'] ?? 85,
            'is_published' => $attributes['is_published'] ?? true,
        ]);
    }

    private function createSavedSearch(User $client, array $attributes = []): SavedPartSearch
    {
        return SavedPartSearch::query()->create([
            'user_id' => $client->id,
            'vehicle_brand' => $attributes['vehicle_brand'] ?? 'Renault',
            'vehicle_model' => $attributes['vehicle_model'] ?? 'Clio',
            'vehicle_year' => $attributes['vehicle_year'] ?? null,
            'part_name' => $attributes['part_name'] ?? 'Alternateur',
            'part_category' => $attributes['part_category'] ?? null,
            'status' => $attributes['status'] ?? 'active',
            'matched_part_id' => $attributes['matched_part_id'] ?? null,
            'matched_at' => $attributes['matched_at'] ?? null,
        ]);
    }
}
