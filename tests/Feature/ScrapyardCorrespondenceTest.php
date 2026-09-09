<?php

namespace Tests\Feature;

use App\Models\Part;
use App\Models\PartHoldRequest;
use App\Models\SavedPartSearch;
use App\Models\Scrapyard;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ScrapyardCorrespondenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_scrapyard_sees_correspondence_for_own_matched_part_without_creating_hold_request(): void
    {
        $client = $this->createClientUser([
            'name' => 'Client Confidentiel',
            'email' => 'client-confidentiel@example.com',
            'phone' => '0696000000',
        ]);
        [$scrapyardUser, $scrapyard] = $this->createScrapyardAccount('Casse Correspondance');
        $savedSearch = $this->createSavedSearch($client, [
            'vehicle_brand' => 'Renault',
            'vehicle_model' => 'Clio 4',
            'part_name' => 'Amortisseur',
        ]);
        $part = $this->createPart(
            $this->createVehicle($scrapyard, ['brand' => 'Renault', 'model' => 'Clio 4']),
            [
                'name' => 'Amortisseur avant',
                'status' => 'preparing',
                'is_published' => false,
            ]
        );

        $this->actingAs($scrapyardUser)
            ->post(route('scrapyard.parts.publish', $part))
            ->assertRedirect(route('scrapyard.parts.show', $part));

        $this->assertSame('matched', $savedSearch->fresh()->status);
        $this->assertSame($part->id, $savedSearch->fresh()->matched_part_id);
        $this->assertSame(0, PartHoldRequest::query()->count());

        $this->actingAs($scrapyardUser)
            ->get(route('scrapyard.correspondences.index'))
            ->assertOk()
            ->assertSee('Correspondances')
            ->assertSee('Un client recherche une pièce correspondant à votre stock.')
            ->assertSee('Amortisseur')
            ->assertSee('Renault Clio 4')
            ->assertSee('Amortisseur avant')
            ->assertSee('Correspondance trouvée')
            ->assertSee(route('scrapyard.parts.show', $part), false);
    }

    public function test_scrapyard_does_not_see_correspondences_from_another_scrapyard(): void
    {
        $client = $this->createClientUser();
        [$scrapyardUserA, $scrapyardA] = $this->createScrapyardAccount('Casse A');
        [, $scrapyardB] = $this->createScrapyardAccount('Casse B');
        $partA = $this->createPart(
            $this->createVehicle($scrapyardA, ['brand' => 'Renault', 'model' => 'Clio']),
            ['name' => 'Alternateur Casse A']
        );
        $partB = $this->createPart(
            $this->createVehicle($scrapyardB, ['brand' => 'Peugeot', 'model' => '208']),
            ['name' => 'Alternateur Casse B']
        );

        $this->createSavedSearch($client, [
            'part_name' => 'Recherche visible',
            'matched_part_id' => $partA->id,
            'status' => 'matched',
            'matched_at' => now(),
        ]);
        $this->createSavedSearch($client, [
            'part_name' => 'Recherche autre casse',
            'matched_part_id' => $partB->id,
            'status' => 'matched',
            'matched_at' => now(),
        ]);

        $this->actingAs($scrapyardUserA)
            ->get(route('scrapyard.correspondences.index'))
            ->assertOk()
            ->assertSee('Recherche visible')
            ->assertSee('Alternateur Casse A')
            ->assertDontSee('Recherche autre casse')
            ->assertDontSee('Alternateur Casse B');
    }

    public function test_client_contact_details_are_never_exposed_on_scrapyard_correspondences_page(): void
    {
        $client = $this->createClientUser([
            'name' => 'Client Secret',
            'email' => 'secret-client@example.com',
            'phone' => '0696123456',
        ]);
        [$scrapyardUser, $scrapyard] = $this->createScrapyardAccount('Casse Confidentialité');
        $part = $this->createPart($this->createVehicle($scrapyard), ['name' => 'Phare avant']);

        $this->createSavedSearch($client, [
            'part_name' => 'Phare',
            'matched_part_id' => $part->id,
            'status' => 'matched',
            'matched_at' => now(),
        ]);

        $this->actingAs($scrapyardUser)
            ->get(route('scrapyard.correspondences.index'))
            ->assertOk()
            ->assertSee('Phare')
            ->assertDontSee('Client Secret')
            ->assertDontSee('secret-client@example.com')
            ->assertDontSee('0696123456');
    }

    public function test_active_search_without_matched_part_is_not_displayed(): void
    {
        $client = $this->createClientUser();
        [$scrapyardUser] = $this->createScrapyardAccount('Casse Active');

        $this->createSavedSearch($client, [
            'part_name' => 'Besoin actif sans correspondance',
            'status' => 'active',
            'matched_part_id' => null,
        ]);

        $this->actingAs($scrapyardUser)
            ->get(route('scrapyard.correspondences.index'))
            ->assertOk()
            ->assertSee('Aucune correspondance pour le moment.')
            ->assertDontSee('Besoin actif sans correspondance');
    }

    public function test_client_cannot_access_scrapyard_correspondences_page(): void
    {
        $client = $this->createClientUser();

        $this->actingAs($client)
            ->get(route('scrapyard.correspondences.index'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_from_scrapyard_correspondences_page(): void
    {
        $this->get(route('scrapyard.correspondences.index'))
            ->assertRedirect(route('login'));
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
     * @return array{0: User, 1: Scrapyard}
     */
    private function createScrapyardAccount(string $name): array
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '-', $name)) . '-' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

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
