<?php

namespace Tests\Feature;

use App\Models\Scrapyard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ScrapyardAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible_to_guests(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Connexion à Pièce Radar')
            ->assertSee('Email')
            ->assertSee('Mot de passe');
    }

    public function test_scrapyard_user_can_log_in(): void
    {
        $user = $this->createScrapyardUserWithScrapyard();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertRedirect(route('scrapyard.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_password_is_rejected(): void
    {
        $user = $this->createScrapyardUserWithScrapyard();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_disconnects_user(): void
    {
        $user = $this->createScrapyardUserWithScrapyard();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login_when_accessing_scrapyard_dashboard(): void
    {
        $this->get(route('scrapyard.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_when_accessing_sensitive_scrapyard_route(): void
    {
        $this->get(route('scrapyard.vehicles.create'))
            ->assertRedirect(route('login'));
    }

    public function test_client_user_cannot_access_scrapyard_area(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user)
            ->get(route('scrapyard.dashboard'))
            ->assertForbidden();
    }

    public function test_scrapyard_user_without_scrapyard_cannot_access_scrapyard_area(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);
        $user->forceFill([
            'role' => 'scrapyard',
        ])->save();

        $this->actingAs($user)
            ->get(route('scrapyard.dashboard'))
            ->assertForbidden();
    }

    public function test_scrapyard_user_with_scrapyard_can_access_dashboard(): void
    {
        $user = $this->createScrapyardUserWithScrapyard();

        $this->actingAs($user)
            ->get(route('scrapyard.dashboard'))
            ->assertOk()
            ->assertSee('Tableau de bord casse');
    }

    private function createScrapyardUserWithScrapyard(): User
    {
        $user = User::factory()->create([
            'name' => 'Casse Test',
            'email' => 'casse-' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->forceFill([
            'role' => 'scrapyard',
        ])->save();

        Scrapyard::query()->create([
            'user_id' => $user->id,
            'name' => 'Casse Martinique',
            'slug' => 'casse-' . uniqid(),
            'city' => 'Fort-de-France',
            'is_active' => true,
        ]);

        return $user;
    }
}
