<?php

namespace Tests\Feature;

use App\Models\Part;
use App\Models\PartHoldRequest;
use App\Models\ProfessionalProfile;
use App\Models\SavedPartSearch;
use App\Models\Scrapyard;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfessionalAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_can_login(): void
    {
        $professional = $this->createProfessionalUser([
            'email' => 'pro-login@example.com',
            'password' => Hash::make('Test1234!'),
        ]);

        $this->post(route('login'), [
            'email' => $professional->email,
            'password' => 'Test1234!',
        ])
            ->assertRedirect(route('professional.account.show'));
    }

    public function test_professional_can_access_public_search(): void
    {
        $professional = $this->createProfessionalUser();

        $this->actingAs($professional)
            ->get(route('client.parts.index'))
            ->assertOk()
            ->assertSee('Résultats de recherche')
            ->assertSee('Espace pro');
    }

    public function test_professional_can_create_part_hold_request(): void
    {
        $professional = $this->createProfessionalUser([
            'name' => 'Garage Pro',
            'email' => 'garage-pro-demande@example.com',
            'phone' => '0696000013',
        ]);
        [, $scrapyard] = $this->createScrapyardAccount('Casse Pro Demande');
        $part = $this->createPart($this->createVehicle($scrapyard), [
            'name' => 'Alternateur professionnel',
        ]);

        $this->actingAs($professional)
            ->get(route('pieces.show', $part))
            ->assertOk()
            ->assertSee('Demander une mise de côté')
            ->assertSee(route('pieces.request', $part), false);

        $this->actingAs($professional)
            ->get(route('pieces.request', $part))
            ->assertOk()
            ->assertSee('Compte professionnel')
            ->assertSee('Garage Pro')
            ->assertSee('garage-pro-demande@example.com')
            ->assertSee('action="' . route('pieces.request.store', $part) . '"', false);

        $this->actingAs($professional)
            ->from(route('pieces.request', $part))
            ->post(route('pieces.request.store', $part), [
                'customer_message' => 'Demande garage pour un client.',
            ])
            ->assertRedirect(route('pieces.show', $part))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('part_hold_requests', [
            'user_id' => $professional->id,
            'part_id' => $part->id,
            'status' => 'pending',
            'customer_message' => 'Demande garage pour un client.',
        ]);

        $this->actingAs($professional)
            ->get(route('client.requests.index'))
            ->assertOk()
            ->assertSee('Alternateur professionnel')
            ->assertSee('Demande garage pour un client.');
    }

    public function test_client_can_still_create_part_hold_request(): void
    {
        $client = $this->createUserWithRole('client');
        [, $scrapyard] = $this->createScrapyardAccount('Casse Client Demande');
        $part = $this->createPart($this->createVehicle($scrapyard), [
            'name' => 'Phare client',
        ]);

        $this->actingAs($client)
            ->from(route('pieces.request', $part))
            ->post(route('pieces.request.store', $part), [
                'customer_message' => 'Demande client classique.',
            ])
            ->assertRedirect(route('pieces.show', $part))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('part_hold_requests', [
            'user_id' => $client->id,
            'part_id' => $part->id,
            'status' => 'pending',
            'customer_message' => 'Demande client classique.',
        ]);
    }

    public function test_scrapyard_and_admin_cannot_create_buyer_part_hold_request(): void
    {
        $scrapyardUser = $this->createUserWithRole('scrapyard');
        $admin = $this->createUserWithRole('admin');
        [, $scrapyard] = $this->createScrapyardAccount('Casse Interdite Demande');
        $part = $this->createPart($this->createVehicle($scrapyard), [
            'name' => 'Démarreur interdit',
        ]);

        $this->actingAs($scrapyardUser)
            ->get(route('pieces.request', $part))
            ->assertForbidden();

        $this->actingAs($scrapyardUser)
            ->post(route('pieces.request.store', $part), [
                'customer_message' => 'Tentative casse.',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('pieces.request', $part))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('pieces.request.store', $part), [
                'customer_message' => 'Tentative admin.',
            ])
            ->assertForbidden();

        $this->assertSame(0, PartHoldRequest::query()->count());
    }

    public function test_professional_can_access_own_requests(): void
    {
        $professional = $this->createProfessionalUser();
        $ownRequest = $this->createHoldRequest($professional, 'Phare pro visible');

        $this->actingAs($professional)
            ->get(route('client.requests.index'))
            ->assertOk()
            ->assertSee('Mes demandes')
            ->assertSee($ownRequest->part->name);
    }

    public function test_professional_can_access_own_saved_searches(): void
    {
        $professional = $this->createProfessionalUser();

        $this->actingAs($professional)
            ->post(route('client.saved-searches.store'), [
                'part_name' => 'Démarreur pro',
                'vehicle_brand' => 'Renault',
                'vehicle_model' => 'Clio',
                'vehicle_year' => 2018,
            ])
            ->assertRedirect(route('client.saved-searches.index'));

        $this->actingAs($professional)
            ->get(route('client.saved-searches.index'))
            ->assertOk()
            ->assertSee('Démarreur pro');
    }

    public function test_professional_can_access_notifications(): void
    {
        $professional = $this->createProfessionalUser();

        $this->actingAs($professional)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Notifications');
    }

    public function test_professional_sees_only_own_requests_and_saved_searches(): void
    {
        $professional = $this->createProfessionalUser();
        $otherProfessional = $this->createProfessionalUser(['email' => 'autre-pro@example.com']);

        $ownRequest = $this->createHoldRequest($professional, 'Demande visible pro');
        $otherRequest = $this->createHoldRequest($otherProfessional, 'Demande autre pro');
        $ownSearch = $this->createSavedSearch($professional, 'Recherche visible pro');
        $otherSearch = $this->createSavedSearch($otherProfessional, 'Recherche autre pro');

        $this->actingAs($professional)
            ->get(route('client.requests.index'))
            ->assertOk()
            ->assertSee($ownRequest->part->name)
            ->assertDontSee($otherRequest->part->name);

        $this->actingAs($professional)
            ->get(route('client.saved-searches.index'))
            ->assertOk()
            ->assertSee($ownSearch->part_name)
            ->assertDontSee($otherSearch->part_name);
    }

    public function test_professional_cannot_access_scrapyard_or_admin_routes(): void
    {
        $professional = $this->createProfessionalUser();

        $this->actingAs($professional)
            ->get(route('scrapyard.dashboard'))
            ->assertForbidden();

        $this->actingAs($professional)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_client_and_scrapyard_cannot_access_professional_account(): void
    {
        $client = $this->createUserWithRole('client');
        $scrapyardUser = $this->createUserWithRole('scrapyard');

        $this->actingAs($client)
            ->get(route('professional.account.show'))
            ->assertForbidden();

        $this->actingAs($scrapyardUser)
            ->get(route('professional.account.show'))
            ->assertForbidden();
    }

    public function test_professional_account_displays_business_information(): void
    {
        $professional = $this->createProfessionalUser([
            'name' => 'Responsable Garage',
            'email' => 'garage@example.com',
            'phone' => '0696000003',
        ]);
        $profile = ProfessionalProfile::query()->create([
            'user_id' => $professional->id,
            'company_name' => 'Garage Martinique',
            'siret' => '12345678900010',
            'address' => '12 rue de l’atelier',
            'postal_code' => '97232',
            'city' => 'Le Lamentin',
        ]);

        $this->actingAs($professional)
            ->get(route('professional.account.show'))
            ->assertOk()
            ->assertSee('Espace professionnel')
            ->assertSee('Garage / Mécanicien')
            ->assertSee('Responsable Garage')
            ->assertSee('garage@example.com')
            ->assertSee('0696000003')
            ->assertSee($profile->company_name)
            ->assertSee($profile->siret)
            ->assertSee($profile->address)
            ->assertSee($profile->postal_code)
            ->assertSee($profile->city);
    }

    public function test_professional_can_update_profile_with_validation_and_ownership(): void
    {
        $professional = $this->createProfessionalUser([
            'name' => 'Responsable Initial',
            'email' => 'pro-original@example.com',
            'phone' => '0696000003',
        ]);
        $otherProfessional = $this->createProfessionalUser(['email' => 'autre-owner@example.com']);
        $otherProfile = ProfessionalProfile::query()->create([
            'user_id' => $otherProfessional->id,
            'company_name' => 'Autre Garage',
            'city' => 'Sainte-Marie',
        ]);

        $this->actingAs($professional)
            ->patch(route('professional.account.update'), [
                'name' => 'Responsable Modifié',
                'phone' => '0696112233',
                'email' => 'pro-modified@example.com',
                'role' => 'admin',
                'user_id' => $otherProfessional->id,
            ])
            ->assertRedirect(route('professional.account.show'))
            ->assertSessionHas('success');

        $this->actingAs($professional)
            ->patch(route('professional.account.profile.update'), [
                'company_name' => 'Garage Pro Modifié',
                'siret' => '98765432100011',
                'address' => '24 avenue pro',
                'postal_code' => '97200',
                'city' => 'Fort-de-France',
                'user_id' => $otherProfessional->id,
                'role' => 'scrapyard',
            ])
            ->assertRedirect(route('professional.account.show'))
            ->assertSessionHas('success');

        $professional->refresh();
        $profile = $professional->professionalProfile()->firstOrFail();

        $this->assertSame('Responsable Modifié', $professional->name);
        $this->assertSame('0696112233', $professional->phone);
        $this->assertSame('pro-original@example.com', $professional->email);
        $this->assertSame('professional', $professional->role);
        $this->assertSame($professional->id, $profile->user_id);
        $this->assertSame('Garage Pro Modifié', $profile->company_name);
        $this->assertSame('Autre Garage', $otherProfile->fresh()->company_name);

        $this->actingAs($professional)
            ->from(route('professional.account.show'))
            ->patch(route('professional.account.profile.update'), [
                'company_name' => '',
                'siret' => str_repeat('1', 21),
                'postal_code' => str_repeat('2', 21),
            ])
            ->assertRedirect(route('professional.account.show'))
            ->assertSessionHasErrors(['company_name', 'siret', 'postal_code']);

        $this->assertSame('Garage Pro Modifié', $profile->fresh()->company_name);
    }

    private function createProfessionalUser(array $attributes = []): User
    {
        return $this->createUserWithRole('professional', $attributes);
    }

    private function createUserWithRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ], $attributes));

        $user->forceFill(['role' => $role])->save();

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
            'phone' => '0596000000',
            'email' => 'contact-' . uniqid() . '@example.com',
            'city' => 'Fort-de-France',
            'is_active' => true,
        ]);

        return [$user, $scrapyard];
    }

    private function createVehicle(Scrapyard $scrapyard): Vehicle
    {
        return Vehicle::query()->create([
            'scrapyard_id' => $scrapyard->id,
            'brand' => 'Renault',
            'model' => 'Clio',
            'year' => 2018,
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
            'category' => $attributes['category'] ?? 'Moteur',
            'condition' => 'used_good',
            'status' => $attributes['status'] ?? 'available',
            'price' => 85,
            'is_published' => $attributes['is_published'] ?? true,
        ]);
    }

    private function createHoldRequest(User $user, string $partName): PartHoldRequest
    {
        [, $scrapyard] = $this->createScrapyardAccount('Casse ' . uniqid());

        return PartHoldRequest::query()->create([
            'user_id' => $user->id,
            'part_id' => $this->createPart($this->createVehicle($scrapyard), ['name' => $partName])->id,
            'status' => 'pending',
            'customer_message' => 'Demande professionnelle.',
        ]);
    }

    private function createSavedSearch(User $user, string $partName): SavedPartSearch
    {
        return SavedPartSearch::query()->create([
            'user_id' => $user->id,
            'vehicle_brand' => 'Renault',
            'vehicle_model' => 'Clio',
            'part_name' => $partName,
            'status' => 'active',
        ]);
    }
}
