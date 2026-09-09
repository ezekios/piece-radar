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

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = $this->createUserWithRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Tableau de bord admin')
            ->assertSee('Administrateur');
    }

    public function test_non_admin_roles_cannot_access_admin_dashboard(): void
    {
        $client = $this->createUserWithRole('client');
        $professional = $this->createUserWithRole('professional');
        $scrapyardUser = $this->createUserWithRole('scrapyard');

        $this->actingAs($client)
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->actingAs($professional)
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->actingAs($scrapyardUser)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_dashboard_displays_indicators(): void
    {
        $admin = $this->createUserWithRole('admin');
        $client = $this->createUserWithRole('client');
        $professional = $this->createUserWithRole('professional');
        $scrapyard = $this->createScrapyardAccount('Casse Stats')[1];
        $vehicle = $this->createVehicle($scrapyard);
        $part = $this->createPart($vehicle);

        PartHoldRequest::query()->create([
            'user_id' => $client->id,
            'part_id' => $part->id,
            'status' => 'pending',
        ]);
        SavedPartSearch::query()->create([
            'user_id' => $professional->id,
            'vehicle_brand' => 'Renault',
            'vehicle_model' => 'Clio',
            'part_name' => 'Alternateur',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Utilisateurs')
            ->assertSee('Clients')
            ->assertSee('Professionnels')
            ->assertSee('Casses')
            ->assertSee('Véhicules')
            ->assertSee('Pièces')
            ->assertSee('Demandes')
            ->assertSee('Recherches sauvegardées')
            ->assertSee((string) User::query()->count())
            ->assertSee((string) PartHoldRequest::query()->count())
            ->assertSee((string) SavedPartSearch::query()->count());
    }

    public function test_admin_can_consult_users(): void
    {
        $admin = $this->createUserWithRole('admin', [
            'name' => 'Admin Liste',
            'email' => 'admin-liste@example.com',
        ]);
        $professional = $this->createUserWithRole('professional', [
            'name' => 'Garage Liste',
            'email' => 'garage-liste@example.com',
        ]);
        ProfessionalProfile::query()->create([
            'user_id' => $professional->id,
            'company_name' => 'Garage Liste SARL',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Utilisateurs')
            ->assertSee('Admin Liste')
            ->assertSee('admin-liste@example.com')
            ->assertSee('Administrateur')
            ->assertSee('Garage Liste')
            ->assertSee('garage-liste@example.com')
            ->assertSee('Garage / Mécanicien');
    }

    public function test_admin_can_consult_scrapyards(): void
    {
        $admin = $this->createUserWithRole('admin');
        [, $scrapyard] = $this->createScrapyardAccount('Casse Admin', [
            'phone' => '0596000000',
            'email' => 'casse-admin@example.com',
            'city' => 'Fort-de-France',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.scrapyards.index'))
            ->assertOk()
            ->assertSee('Casses')
            ->assertSee($scrapyard->name)
            ->assertSee('Fort-de-France')
            ->assertSee('casse-admin@example.com')
            ->assertSee('0596000000')
            ->assertSee('Active');
    }

    public function test_admin_can_update_scrapyard_active_status(): void
    {
        $admin = $this->createUserWithRole('admin');
        [, $scrapyard] = $this->createScrapyardAccount('Casse Statut', [
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.scrapyards.update-status', $scrapyard), [
                'is_active' => false,
            ])
            ->assertRedirect(route('admin.scrapyards.index'))
            ->assertSessionHas('success');

        $this->assertFalse($scrapyard->fresh()->is_active);
    }

    public function test_non_admin_cannot_update_scrapyard_active_status(): void
    {
        $client = $this->createUserWithRole('client');
        [, $scrapyard] = $this->createScrapyardAccount('Casse Protegee', [
            'is_active' => true,
        ]);

        $this->actingAs($client)
            ->patch(route('admin.scrapyards.update-status', $scrapyard), [
                'is_active' => false,
            ])
            ->assertForbidden();

        $this->assertTrue($scrapyard->fresh()->is_active);
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
    private function createScrapyardAccount(string $name, array $scrapyardAttributes = []): array
    {
        $user = $this->createUserWithRole('scrapyard', [
            'name' => $name,
            'email' => strtolower(str_replace(' ', '-', $name)) . '-' . uniqid() . '@example.com',
        ]);

        $scrapyard = Scrapyard::query()->create(array_merge([
            'user_id' => $user->id,
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)) . '-' . uniqid(),
            'phone' => '0596000000',
            'email' => 'contact-' . uniqid() . '@example.com',
            'city' => 'Fort-de-France',
            'is_active' => true,
        ], $scrapyardAttributes));

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

    private function createPart(Vehicle $vehicle): Part
    {
        return Part::query()->create([
            'vehicle_id' => $vehicle->id,
            'name' => 'Alternateur',
            'category' => 'Moteur',
            'condition' => 'used_good',
            'status' => 'available',
            'price' => 85,
            'is_published' => true,
        ]);
    }
}
