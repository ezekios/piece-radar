<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClientAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_client_account(): void
    {
        $this->get(route('client.account.show'))
            ->assertRedirect(route('login'));
    }

    public function test_verified_client_can_access_own_account(): void
    {
        $client = $this->createClientUser([
            'name' => 'Client Exemple',
            'email' => 'client-compte@example.com',
            'phone' => '0696000000',
        ]);

        $response = $this->actingAs($client)
            ->get(route('client.account.show'));

        $response
            ->assertOk()
            ->assertSee('Mon compte')
            ->assertSee('Client Exemple')
            ->assertSee('client-compte@example.com')
            ->assertSee('0696000000')
            ->assertSee('Client')
            ->assertSee('Mes demandes')
            ->assertSee('aria-label="Navigation mobile client"', false)
            ->assertSee('fixed inset-x-0 bottom-0 z-40 border-t border-zinc-200 bg-white/95 px-3 py-2', false)
            ->assertSee(route('home'), false)
            ->assertSee(route('client.parts.index'), false)
            ->assertSee(route('client.requests.index'), false)
            ->assertSee(route('client.account.show'), false)
            ->assertSee('aria-current="page"', false);

        $html = $response->getContent();

        $this->assertSame(3, substr_count($html, "data-password-toggle\n"));
        $this->assertMatchesRegularExpression('/id="current_password"\s+name="current_password"\s+type="password"/', $html);
        $this->assertMatchesRegularExpression('/id="password"\s+name="password"\s+type="password"/', $html);
        $this->assertMatchesRegularExpression('/id="password_confirmation"\s+name="password_confirmation"\s+type="password"/', $html);
        $this->assertSame(3, substr_count($html, "type=\"button\"\n                                    class=\"absolute"));
        $this->assertStringContainsString('aria-label="Afficher le mot de passe"', $html);
        $this->assertStringContainsString('data-password-target="current_password"', $html);
        $this->assertStringContainsString('data-password-target="password"', $html);
        $this->assertStringContainsString('data-password-target="password_confirmation"', $html);
    }

    public function test_client_navigation_links_to_account_page(): void
    {
        $client = $this->createClientUser();

        $this->actingAs($client)
            ->get(route('client.parts.index'))
            ->assertOk()
            ->assertSee(route('client.account.show'), false)
            ->assertSee('Compte');
    }

    public function test_scrapyard_user_cannot_access_client_account(): void
    {
        $scrapyardUser = $this->createScrapyardUser();

        $this->actingAs($scrapyardUser)
            ->get(route('client.account.show'))
            ->assertForbidden();
    }

    public function test_client_can_update_name_and_phone_without_changing_protected_fields(): void
    {
        $client = $this->createClientUser([
            'email' => 'client-original@example.com',
            'password' => Hash::make('original-password'),
            'phone' => '0696000000',
        ]);

        $this->actingAs($client)
            ->patch(route('client.account.update'), [
                'name' => 'Client Modifié',
                'phone' => '0696112233',
                'email' => 'client-modified@example.com',
                'role' => 'scrapyard',
                'password' => 'plain-password',
                'user_id' => 999,
            ])
            ->assertRedirect(route('client.account.show'))
            ->assertSessionHas('success');

        $client->refresh();

        $this->assertSame('Client Modifié', $client->name);
        $this->assertSame('0696112233', $client->phone);
        $this->assertSame('client-original@example.com', $client->email);
        $this->assertSame('client', $client->role);
        $this->assertTrue(Hash::check('original-password', $client->password));
    }

    public function test_invalid_client_account_data_is_rejected(): void
    {
        $client = $this->createClientUser([
            'name' => 'Client Valide',
            'phone' => '0696000000',
        ]);

        $this->actingAs($client)
            ->from(route('client.account.show'))
            ->patch(route('client.account.update'), [
                'name' => '',
                'phone' => str_repeat('1', 31),
            ])
            ->assertRedirect(route('client.account.show'))
            ->assertSessionHasErrors(['name', 'phone']);

        $client->refresh();

        $this->assertSame('Client Valide', $client->name);
        $this->assertSame('0696000000', $client->phone);
    }

    public function test_client_can_update_password_with_correct_current_password(): void
    {
        $client = $this->createClientUser([
            'password' => Hash::make('current-password'),
        ]);

        $this->actingAs($client)
            ->patch(route('client.account.password.update'), [
                'current_password' => 'current-password',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ])
            ->assertRedirect(route('client.account.show'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('NewPassword123!', $client->fresh()->password));
        $this->assertFalse(Hash::check('current-password', $client->fresh()->password));
    }

    public function test_client_password_update_is_rejected_when_current_password_is_wrong(): void
    {
        $client = $this->createClientUser([
            'password' => Hash::make('current-password'),
        ]);

        $this->actingAs($client)
            ->from(route('client.account.show'))
            ->patch(route('client.account.password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'NewPassword123!',
                'password_confirmation' => 'NewPassword123!',
            ])
            ->assertRedirect(route('client.account.show'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('current-password', $client->fresh()->password));
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
}
