<?php

namespace Tests\Feature;

use App\Models\Scrapyard;
use App\Models\User;
use App\Notifications\Auth\VerifyEmailNotification as VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ScrapyardRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_scrapyard_registration_form_is_publicly_accessible(): void
    {
        $this->get(route('scrapyard.register.create'))
            ->assertOk()
            ->assertSee('Créer un compte casse')
            ->assertSee('Nom de la casse')
            ->assertSee('SIRET')
            ->assertSee('Email de connexion');
    }

    public function test_valid_scrapyard_registration_creates_inactive_unverified_scrapyard_account(): void
    {
        Notification::fake();

        $this->post(route('scrapyard.register.store'), $this->validRegistrationData([
            'role' => 'admin',
            'is_active' => true,
            'user_id' => 999,
        ]))
            ->assertRedirect(route('verification.notice'));

        $user = User::query()->where('email', 'casse-inscription@example.com')->firstOrFail();
        $scrapyard = $user->scrapyard()->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('scrapyard', $user->role);
        $this->assertNull($user->email_verified_at);
        $this->assertSame('Responsable Casse', $user->name);
        $this->assertSame('0696000010', $user->phone);
        $this->assertTrue(Hash::check('SecurePassword123!', $user->password));
        $this->assertSame($user->id, $scrapyard->user_id);
        $this->assertSame('Casse Inscription', $scrapyard->name);
        $this->assertSame('12345678901234', $scrapyard->siret);
        $this->assertSame('contact-casse@example.com', $scrapyard->email);
        $this->assertSame('0596000010', $scrapyard->phone);
        $this->assertSame('12 rue des pièces', $scrapyard->address);
        $this->assertSame('97200', $scrapyard->postal_code);
        $this->assertSame('Fort-de-France', $scrapyard->city);
        $this->assertSame('Casse automobile de démonstration.', $scrapyard->description);
        $this->assertFalse($scrapyard->is_active);
        $this->assertNotSame(999, $scrapyard->user_id);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_scrapyard_registration_requires_siret(): void
    {
        $data = $this->validRegistrationData([
            'siret' => '',
        ]);

        $this->from(route('scrapyard.register.create'))
            ->post(route('scrapyard.register.store'), $data)
            ->assertRedirect(route('scrapyard.register.create'))
            ->assertSessionHasErrors('siret');

        $this->assertDatabaseMissing('users', [
            'email' => 'casse-inscription@example.com',
        ]);
        $this->assertSame(0, Scrapyard::query()->count());
    }

    public function test_scrapyard_registration_requires_exactly_fourteen_siret_digits(): void
    {
        foreach (['1234567890123', '123456789012345', '1234567890123A'] as $siret) {
            $this->from(route('scrapyard.register.create'))
                ->post(route('scrapyard.register.store'), $this->validRegistrationData([
                    'email' => 'casse-' . $siret . '@example.com',
                    'siret' => $siret,
                ]))
                ->assertRedirect(route('scrapyard.register.create'))
                ->assertSessionHasErrors('siret');
        }

        $this->assertSame(0, Scrapyard::query()->count());
    }

    public function test_scrapyard_registration_rejects_duplicated_siret(): void
    {
        $this->createScrapyardAccount('Casse Siret Existant', true, [], [
            'siret' => '12345678901234',
        ]);

        $this->from(route('scrapyard.register.create'))
            ->post(route('scrapyard.register.store'), $this->validRegistrationData([
                'email' => 'nouvelle-casse@example.com',
                'siret' => '12345678901234',
            ]))
            ->assertRedirect(route('scrapyard.register.create'))
            ->assertSessionHasErrors('siret');

        $this->assertDatabaseMissing('users', [
            'email' => 'nouvelle-casse@example.com',
        ]);
    }

    public function test_scrapyard_registration_requires_unique_user_email(): void
    {
        User::factory()->create([
            'email' => 'casse-inscription@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->from(route('scrapyard.register.create'))
            ->post(route('scrapyard.register.store'), $this->validRegistrationData())
            ->assertRedirect(route('scrapyard.register.create'))
            ->assertSessionHasErrors('email');

        $this->assertSame(1, User::query()->where('email', 'casse-inscription@example.com')->count());
        $this->assertSame(0, Scrapyard::query()->count());
    }

    public function test_invalid_scrapyard_data_does_not_create_orphan_user(): void
    {
        $data = $this->validRegistrationData([
            'scrapyard_name' => '',
        ]);

        $this->from(route('scrapyard.register.create'))
            ->post(route('scrapyard.register.store'), $data)
            ->assertRedirect(route('scrapyard.register.create'))
            ->assertSessionHasErrors('scrapyard_name');

        $this->assertDatabaseMissing('users', [
            'email' => 'casse-inscription@example.com',
        ]);
        $this->assertSame(0, Scrapyard::query()->count());
    }

    public function test_unverified_scrapyard_is_redirected_to_email_verification_before_business_area(): void
    {
        $user = $this->createScrapyardAccount('Casse Email Non Verifie', false, [
            'email_verified_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('scrapyard.dashboard'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->get(route('scrapyard.vehicles.index'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->get(route('scrapyard.account.show'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)
            ->get(route('scrapyard.pending'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_verified_inactive_scrapyard_is_redirected_to_pending_page_instead_of_business_area(): void
    {
        $user = $this->createScrapyardAccount('Casse En Attente', false);

        $this->actingAs($user)
            ->get(route('scrapyard.dashboard'))
            ->assertRedirect(route('scrapyard.pending'));

        $this->actingAs($user)
            ->get(route('scrapyard.vehicles.index'))
            ->assertRedirect(route('scrapyard.pending'));

        $this->actingAs($user)
            ->get(route('scrapyard.account.show'))
            ->assertRedirect(route('scrapyard.pending'));

        $this->actingAs($user)
            ->get(route('scrapyard.pending'))
            ->assertOk()
            ->assertSee('Votre compte professionnel est en attente de validation.')
            ->assertSee('Un administrateur doit valider votre casse')
            ->assertSee('SIRET déclaré')
            ->assertSee('Email vérifié');
    }

    public function test_unverified_scrapyard_login_redirects_to_verification_notice(): void
    {
        $user = $this->createScrapyardAccount('Casse Login Verification', false, [
            'email' => 'casse-login-verification@example.com',
            'email_verified_at' => null,
            'password' => Hash::make('password'),
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertRedirect(route('verification.notice'));
    }

    public function test_verified_inactive_scrapyard_login_redirects_to_pending_page(): void
    {
        $user = $this->createScrapyardAccount('Casse Login Attente', false, [
            'email' => 'casse-login-attente@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertRedirect(route('scrapyard.pending'));
    }

    public function test_admin_cannot_activate_scrapyard_before_email_verification(): void
    {
        $admin = $this->createUserWithRole('admin');
        $user = $this->createScrapyardAccount('Casse Email Bloque', false, [
            'email_verified_at' => null,
        ]);
        $scrapyard = $user->scrapyard()->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.scrapyards.update-status', $scrapyard), [
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.scrapyards.index'))
            ->assertSessionHas('error', 'Impossible d’activer cette casse tant que l’adresse email du compte n’a pas été vérifiée.');

        $this->assertFalse($scrapyard->fresh()->is_active);
    }

    public function test_after_email_verification_inactive_scrapyard_arrives_on_pending_page(): void
    {
        $user = $this->createScrapyardAccount('Casse Verification Attente', false, [
            'email_verified_at' => null,
        ]);

        $this->actingAs($user)
            ->get($this->verificationUrlFor($user))
            ->assertRedirect(route('scrapyard.pending'))
            ->assertSessionHas('status', 'Votre adresse email a été vérifiée.');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_admin_sees_and_can_activate_verified_new_scrapyard(): void
    {
        Notification::fake();
        $admin = $this->createUserWithRole('admin');

        $this->post(route('scrapyard.register.store'), $this->validRegistrationData())
            ->assertRedirect(route('verification.notice'));

        $scrapyard = Scrapyard::query()->where('name', 'Casse Inscription')->firstOrFail();
        $user = $scrapyard->user;

        $this->actingAs($admin)
            ->patch(route('admin.scrapyards.update-status', $scrapyard), [
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.scrapyards.index'))
            ->assertSessionHas('error', 'Impossible d’activer cette casse tant que l’adresse email du compte n’a pas été vérifiée.');

        $this->actingAs($user)
            ->get($this->verificationUrlFor($user))
            ->assertRedirect(route('scrapyard.pending'));

        $this->actingAs($admin)
            ->get(route('admin.scrapyards.index'))
            ->assertOk()
            ->assertSee('Casse Inscription')
            ->assertSee('12345678901234')
            ->assertSee('Vérifié')
            ->assertSee('En attente')
            ->assertSee('Activer');

        $this->actingAs($admin)
            ->patch(route('admin.scrapyards.update-status', $scrapyard), [
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.scrapyards.index'))
            ->assertSessionHas('success');

        $this->assertTrue($scrapyard->fresh()->is_active);

        $this->actingAs($user->fresh())
            ->get(route('scrapyard.dashboard'))
            ->assertOk()
            ->assertSee('Tableau de bord casse');
    }

    public function test_existing_active_scrapyard_still_accesses_scrapyard_area(): void
    {
        $user = $this->createScrapyardAccount('Casse Active', true);

        $this->actingAs($user)
            ->get(route('scrapyard.dashboard'))
            ->assertOk()
            ->assertSee('Tableau de bord casse');
    }

    public function test_client_professional_and_admin_are_not_redirected_to_scrapyard_pending_page(): void
    {
        $client = $this->createUserWithRole('client');
        $professional = $this->createUserWithRole('professional');
        $admin = $this->createUserWithRole('admin');

        $this->actingAs($client)
            ->get(route('scrapyard.pending'))
            ->assertForbidden();

        $this->actingAs($professional)
            ->get(route('scrapyard.pending'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('scrapyard.pending'))
            ->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validRegistrationData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Responsable Casse',
            'email' => 'casse-inscription@example.com',
            'phone' => '0696000010',
            'password' => 'SecurePassword123!',
            'password_confirmation' => 'SecurePassword123!',
            'scrapyard_name' => 'Casse Inscription',
            'siret' => '12345678901234',
            'scrapyard_email' => 'contact-casse@example.com',
            'scrapyard_phone' => '0596000010',
            'address' => '12 rue des pièces',
            'postal_code' => '97200',
            'city' => 'Fort-de-France',
            'description' => 'Casse automobile de démonstration.',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
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
     * @param  array<string, mixed>  $userAttributes
     */
    private function createScrapyardAccount(string $name, bool $isActive, array $userAttributes = [], array $scrapyardAttributes = []): User
    {
        $user = $this->createUserWithRole('scrapyard', array_merge([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '-', $name)) . '-' . uniqid() . '@example.com',
        ], $userAttributes));

        Scrapyard::query()->create(array_merge([
            'user_id' => $user->id,
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)) . '-' . uniqid(),
            'siret' => (string) fake()->unique()->numerify('##############'),
            'phone' => '0596000000',
            'email' => 'contact-' . uniqid() . '@example.com',
            'city' => 'Fort-de-France',
            'is_active' => $isActive,
        ], $scrapyardAttributes));

        return $user;
    }

    private function verificationUrlFor(User $user): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );
    }
}
