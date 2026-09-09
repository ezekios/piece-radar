<?php

namespace Tests\Feature;

use App\Models\Scrapyard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ScrapyardAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_scrapyard_account(): void
    {
        $this->get(route('scrapyard.account.show'))
            ->assertRedirect(route('login'));
    }

    public function test_client_cannot_access_scrapyard_account(): void
    {
        $client = $this->createClientUser();

        $this->actingAs($client)
            ->get(route('scrapyard.account.show'))
            ->assertForbidden();
    }

    public function test_scrapyard_user_without_scrapyard_gets_controlled_forbidden_response(): void
    {
        $user = $this->createScrapyardUser();

        $this->actingAs($user)
            ->get(route('scrapyard.account.show'))
            ->assertForbidden();
    }

    public function test_scrapyard_user_can_access_own_account_and_scrapyard_information(): void
    {
        [$user, $scrapyard] = $this->createScrapyardAccount('Casse Compte', [
            'email' => 'pro-casse@example.com',
            'phone' => '0596000000',
            'address' => '12 rue des pièces',
            'postal_code' => '97200',
            'city' => 'Fort-de-France',
            'description' => 'Pièces auto de réemploi.',
            'is_active' => true,
        ], [
            'name' => 'Responsable Casse',
            'email' => 'connexion-casse@example.com',
            'phone' => '0696000000',
        ]);

        $response = $this->actingAs($user)
            ->get(route('scrapyard.account.show'));

        $response
            ->assertOk()
            ->assertSee('Mon compte casse')
            ->assertSee('Mon compte professionnel')
            ->assertSee('Responsable Casse')
            ->assertSee('connexion-casse@example.com')
            ->assertSee('0696000000')
            ->assertSee('Professionnel / Casse')
            ->assertSee('Informations de la casse')
            ->assertSee($scrapyard->name)
            ->assertSee('pro-casse@example.com')
            ->assertSee('0596000000')
            ->assertSee('12 rue des pièces')
            ->assertSee('97200')
            ->assertSee('Fort-de-France')
            ->assertSee('Pièces auto de réemploi.')
            ->assertSee('Active')
            ->assertSee('Mon compte')
            ->assertSee('Déconnexion')
            ->assertSee('href="'.route('scrapyard.account.show').'"', false);

        $html = $response->getContent();

        $this->assertSame(3, preg_match_all('/<button[^>]*\bdata-password-toggle\b/s', $html));
        $this->assertMatchesRegularExpression('/id="current_password"\s+name="current_password"\s+type="password"/', $html);
        $this->assertMatchesRegularExpression('/id="password"\s+name="password"\s+type="password"/', $html);
        $this->assertMatchesRegularExpression('/id="password_confirmation"\s+name="password_confirmation"\s+type="password"/', $html);
        $this->assertSame(3, preg_match_all('/type="button"\s+class="absolute/', $html));
        $this->assertStringContainsString('aria-label="Afficher le mot de passe"', $html);
    }

    public function test_scrapyard_user_can_update_account_name_and_phone_without_changing_protected_fields(): void
    {
        [$user] = $this->createScrapyardAccount('Casse Profil', [], [
            'email' => 'scrapyard-original@example.com',
            'password' => Hash::make('original-password'),
            'phone' => '0696000000',
        ]);

        $this->actingAs($user)
            ->patch(route('scrapyard.account.update'), [
                'name' => 'Compte Modifié',
                'phone' => '0696112233',
                'email' => 'scrapyard-modified@example.com',
                'role' => 'client',
                'password' => 'plain-password',
                'user_id' => 999,
                'scrapyard_id' => 999,
            ])
            ->assertRedirect(route('scrapyard.account.show'))
            ->assertSessionHas('success');

        $user->refresh();

        $this->assertSame('Compte Modifié', $user->name);
        $this->assertSame('0696112233', $user->phone);
        $this->assertSame('scrapyard-original@example.com', $user->email);
        $this->assertSame('scrapyard', $user->role);
        $this->assertTrue(Hash::check('original-password', $user->password));
    }

    public function test_scrapyard_user_can_update_authorized_scrapyard_fields_without_changing_protected_fields(): void
    {
        [$user, $scrapyard] = $this->createScrapyardAccount('Casse Initiale', [
            'slug' => 'casse-initiale',
            'is_active' => true,
        ]);
        $originalUserId = $scrapyard->user_id;
        $originalSlug = $scrapyard->slug;

        $this->actingAs($user)
            ->patch(route('scrapyard.account.scrapyard.update'), [
                'name' => 'Casse Modifiée',
                'email' => 'atelier-modifie@example.com',
                'phone' => '0596112233',
                'address' => '24 avenue du stock',
                'postal_code' => '97232',
                'city' => 'Le Lamentin',
                'description' => 'Nouvelle description professionnelle.',
                'id' => 999,
                'user_id' => 999,
                'slug' => 'slug-interdit',
                'is_active' => false,
                'created_at' => now()->subYear()->toDateTimeString(),
                'updated_at' => now()->subYear()->toDateTimeString(),
            ])
            ->assertRedirect(route('scrapyard.account.show'))
            ->assertSessionHas('success');

        $scrapyard->refresh();

        $this->assertSame('Casse Modifiée', $scrapyard->name);
        $this->assertSame('atelier-modifie@example.com', $scrapyard->email);
        $this->assertSame('0596112233', $scrapyard->phone);
        $this->assertSame('24 avenue du stock', $scrapyard->address);
        $this->assertSame('97232', $scrapyard->postal_code);
        $this->assertSame('Le Lamentin', $scrapyard->city);
        $this->assertSame('Nouvelle description professionnelle.', $scrapyard->description);
        $this->assertSame($originalUserId, $scrapyard->user_id);
        $this->assertSame($originalSlug, $scrapyard->slug);
        $this->assertTrue($scrapyard->is_active);
    }

    public function test_invalid_scrapyard_account_data_is_rejected(): void
    {
        [$user, $scrapyard] = $this->createScrapyardAccount('Casse Valide', [
            'email' => 'valid-casse@example.com',
            'phone' => '0596000000',
            'postal_code' => '97200',
            'description' => 'Description valide.',
        ], [
            'name' => 'Compte Valide',
            'phone' => '0696000000',
        ]);

        $this->actingAs($user)
            ->from(route('scrapyard.account.show'))
            ->patch(route('scrapyard.account.update'), [
                'name' => '',
                'phone' => str_repeat('1', 31),
            ])
            ->assertRedirect(route('scrapyard.account.show'))
            ->assertSessionHasErrors(['name', 'phone']);

        $this->actingAs($user)
            ->from(route('scrapyard.account.show'))
            ->patch(route('scrapyard.account.scrapyard.update'), [
                'name' => '',
                'email' => 'not-an-email',
                'phone' => str_repeat('2', 31),
                'postal_code' => str_repeat('3', 21),
                'description' => str_repeat('A', 1001),
            ])
            ->assertRedirect(route('scrapyard.account.show'))
            ->assertSessionHasErrors(['name', 'email', 'phone', 'postal_code', 'description']);

        $user->refresh();
        $scrapyard->refresh();

        $this->assertSame('Compte Valide', $user->name);
        $this->assertSame('0696000000', $user->phone);
        $this->assertSame('Casse Valide', $scrapyard->name);
        $this->assertSame('valid-casse@example.com', $scrapyard->email);
        $this->assertSame('0596000000', $scrapyard->phone);
        $this->assertSame('97200', $scrapyard->postal_code);
        $this->assertSame('Description valide.', $scrapyard->description);
    }

    public function test_scrapyard_user_can_update_password_with_correct_current_password(): void
    {
        [$user] = $this->createScrapyardAccount('Casse Password', [], [
            'password' => Hash::make('current-password'),
        ]);

        $this->actingAs($user)
            ->patch(route('scrapyard.account.password.update'), [
                'current_password' => 'current-password',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ])
            ->assertRedirect(route('scrapyard.account.show'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
        $this->assertFalse(Hash::check('current-password', $user->fresh()->password));
    }

    public function test_scrapyard_password_update_is_rejected_when_current_password_is_wrong(): void
    {
        [$user] = $this->createScrapyardAccount('Casse Wrong Password', [], [
            'password' => Hash::make('current-password'),
        ]);

        $this->actingAs($user)
            ->from(route('scrapyard.account.show'))
            ->patch(route('scrapyard.account.password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ])
            ->assertRedirect(route('scrapyard.account.show'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('current-password', $user->fresh()->password));
    }

    public function test_scrapyard_user_cannot_modify_another_scrapyard_with_account_routes(): void
    {
        [$userA, $scrapyardA] = $this->createScrapyardAccount('Casse A');
        [, $scrapyardB] = $this->createScrapyardAccount('Casse B', [
            'email' => 'casse-b@example.com',
            'slug' => 'casse-b',
        ]);

        $this->actingAs($userA)
            ->patch(route('scrapyard.account.scrapyard.update'), [
                'id' => $scrapyardB->id,
                'name' => 'Casse A Modifiée',
                'email' => 'casse-a-modifiee@example.com',
                'phone' => '0596000001',
                'address' => 'Adresse A',
                'postal_code' => '97200',
                'city' => 'Fort-de-France',
                'description' => 'Description A.',
            ])
            ->assertRedirect(route('scrapyard.account.show'));

        $this->assertSame('Casse A Modifiée', $scrapyardA->fresh()->name);
        $this->assertSame('Casse B', $scrapyardB->fresh()->name);
        $this->assertSame('casse-b@example.com', $scrapyardB->fresh()->email);
        $this->assertSame('casse-b', $scrapyardB->fresh()->slug);
    }

    private function createClientUser(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'email_verified_at' => now(),
        ], $attributes));

        $user->forceFill(['role' => 'client'])->save();

        return $user;
    }

    private function createScrapyardUser(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'email_verified_at' => now(),
        ], $attributes));

        $user->forceFill(['role' => 'scrapyard'])->save();

        return $user;
    }

    /**
     * @param  array<string, mixed>  $scrapyardAttributes
     * @param  array<string, mixed>  $userAttributes
     * @return array{0: User, 1: Scrapyard}
     */
    private function createScrapyardAccount(
        string $name,
        array $scrapyardAttributes = [],
        array $userAttributes = []
    ): array {
        $user = $this->createScrapyardUser(array_merge([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '-', $name)).'-'.uniqid().'@example.com',
            'password' => Hash::make('password'),
            'phone' => '0696000000',
        ], $userAttributes));

        $scrapyard = Scrapyard::query()->create(array_merge([
            'user_id' => $user->id,
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)).'-'.uniqid(),
            'phone' => '0596000000',
            'email' => 'contact-'.uniqid().'@example.com',
            'address' => '1 rue de la casse',
            'postal_code' => '97200',
            'city' => 'Fort-de-France',
            'description' => 'Description de la casse.',
            'is_active' => true,
        ], $scrapyardAttributes));

        return [$user, $scrapyard];
    }
}
