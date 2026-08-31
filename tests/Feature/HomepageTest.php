<?php

namespace Tests\Feature;

use App\Models\Part;
use App\Models\Scrapyard;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_is_accessible_to_guest_and_replaces_laravel_default_content(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('Trouvez la pièce auto qu\'il vous faut')
            ->assertSee('Pièce Radar')
            ->assertDontSee('Laravel News')
            ->assertDontSee('Documentation');
    }

    public function test_homepage_contains_search_link_and_guest_auth_links(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Rechercher une pièce')
            ->assertSee(route('client.parts.index'), false)
            ->assertSee('Connexion')
            ->assertSee('Créer un compte');
    }

    public function test_client_navigation_shows_my_requests_without_scrapyard_cta(): void
    {
        $client = $this->createClientUser();

        $this->actingAs($client)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Mes demandes')
            ->assertSee('Déconnexion')
            ->assertDontSee('Espace casse')
            ->assertDontSee('Accéder à l\'espace casse');
    }

    public function test_scrapyard_navigation_shows_scrapyard_area_without_client_requests(): void
    {
        [$scrapyardUser] = $this->createScrapyardAccount();

        $this->actingAs($scrapyardUser)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Espace casse')
            ->assertSee('Déconnexion')
            ->assertDontSee('Mes demandes');
    }

    public function test_quick_search_form_targets_parts_index_with_supported_get_fields(): void
    {
        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSee('method="GET"', false)
            ->assertSee('action="' . route('client.parts.index') . '"', false)
            ->assertSee('name="q"', false)
            ->assertSee('name="brand"', false)
            ->assertSee('name="model"', false)
            ->assertSee('name="city"', false)
            ->assertDontSee('license_plate', false);
    }

    public function test_recent_parts_show_only_published_available_parts(): void
    {
        [, $scrapyard] = $this->createScrapyardAccount();
        $vehicle = $this->createVehicle($scrapyard);
        $visiblePart = $this->createPart($vehicle, [
            'name' => 'Alternateur visible homepage',
            'status' => 'available',
            'is_published' => true,
        ]);
        $hiddenUnpublishedPart = $this->createPart($vehicle, [
            'name' => 'Phare non publié homepage',
            'status' => 'available',
            'is_published' => false,
        ]);
        $hiddenReservedPart = $this->createPart($vehicle, [
            'name' => 'Pare-chocs réservé homepage',
            'status' => 'reserved',
            'is_published' => true,
        ]);
        $hiddenSoldPart = $this->createPart($vehicle, [
            'name' => 'Capot vendu homepage',
            'status' => 'sold',
            'is_published' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($visiblePart->name)
            ->assertDontSee($hiddenUnpublishedPart->name)
            ->assertDontSee($hiddenReservedPart->name)
            ->assertDontSee($hiddenSoldPart->name);
    }

    public function test_homepage_recent_parts_display_first_photo_when_available(): void
    {
        [, $scrapyard] = $this->createScrapyardAccount();
        $part = $this->createPart($this->createVehicle($scrapyard), [
            'name' => 'Démarreur avec photo homepage',
            'status' => 'available',
            'is_published' => true,
        ]);
        $part->images()->create([
            'path' => 'part-images/homepage-photo.jpg',
            'position' => 1,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Démarreur avec photo homepage')
            ->assertSee('part-images/homepage-photo.jpg');
    }

    public function test_homepage_works_with_no_recent_parts_or_photos(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Aucune pièce disponible publiée pour le moment.');

        [, $scrapyard] = $this->createScrapyardAccount();
        $part = $this->createPart($this->createVehicle($scrapyard), [
            'name' => 'Rétroviseur sans photo homepage',
            'status' => 'available',
            'is_published' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($part->name);
    }

    public function test_public_parts_and_detail_still_work_from_homepage_data(): void
    {
        [, $scrapyard] = $this->createScrapyardAccount();
        $part = $this->createPart($this->createVehicle($scrapyard), [
            'name' => 'Compresseur clim public',
            'status' => 'available',
            'is_published' => true,
        ]);

        $this->get(route('client.parts.index'))
            ->assertOk()
            ->assertSee('Compresseur clim public');

        $this->get(route('pieces.show', $part))
            ->assertOk()
            ->assertSee('Compresseur clim public');
    }

    private function createClientUser(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'name' => 'Client Test',
            'email' => 'client-' . uniqid() . '@example.com',
            'phone' => '0696000000',
            'password' => Hash::make('password'),
        ], $attributes));
        $user->forceFill(['role' => 'client'])->save();

        return $user;
    }

    /**
     * @return array{0: User, 1: Scrapyard}
     */
    private function createScrapyardAccount(string $name = 'Casse Test'): array
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '-', $name)) . '-' . uniqid() . '@example.com',
            'phone' => '0596000000',
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

    private function createVehicle(Scrapyard $scrapyard): Vehicle
    {
        return Vehicle::query()->create([
            'scrapyard_id' => $scrapyard->id,
            'brand' => 'Peugeot',
            'model' => '208',
            'year' => 2019,
            'engine' => '1.2 PureTech',
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
            'status' => $attributes['status'] ?? 'available',
            'price' => 120,
            'is_published' => $attributes['is_published'] ?? true,
        ]);
    }
}
