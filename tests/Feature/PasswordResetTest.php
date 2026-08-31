<?php

namespace Tests\Feature;

use App\Models\Part;
use App\Models\PartHoldRequest;
use App\Models\Scrapyard;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_is_accessible_to_guest(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Mot de passe oublié')
            ->assertSee('Email');
    }

    public function test_forgot_password_email_is_required_and_must_be_valid(): void
    {
        $this->post(route('password.email'), [])
            ->assertSessionHasErrors('email');

        $this->post(route('password.email'), [
            'email' => 'not-an-email',
        ])
            ->assertSessionHasErrors('email');
    }

    public function test_valid_reset_link_request_sends_notification_with_token(): void
    {
        Notification::fake();
        $user = $this->createClientUser([
            'email' => 'client-reset@example.com',
        ]);
        $token = null;

        $this->post(route('password.email'), [
            'email' => $user->email,
        ])
            ->assertSessionHas('status', 'Si un compte correspond à cette adresse, un lien de réinitialisation a été envoyé.');

        Notification::assertSentTo(
            $user,
            ResetPasswordNotification::class,
            function (ResetPasswordNotification $notification) use (&$token): bool {
                $token = $notification->token;

                return is_string($token) && $token !== '';
            }
        );

        $this->assertNotNull($token);
    }

    public function test_reset_form_is_accessible_with_token(): void
    {
        $user = $this->createClientUser([
            'email' => 'client-form@example.com',
        ]);
        $token = app('auth.password.broker')->createToken($user);

        $this->get(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]))
            ->assertOk()
            ->assertSee('Réinitialiser le mot de passe')
            ->assertSee($user->email);
    }

    public function test_client_can_reset_password_and_login_with_new_password(): void
    {
        $user = $this->createClientUser([
            'email' => 'client-new-password@example.com',
        ]);
        $token = app('auth.password.broker')->createToken($user);
        $oldPasswordHash = $user->password;

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Votre mot de passe a été réinitialisé. Vous pouvez maintenant vous connecter.');

        $user->refresh();

        $this->assertNotSame($oldPasswordHash, $user->password);
        $this->assertTrue(Hash::check('new-secret-password', $user->password));
        $this->assertSame('client', $user->role);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertSessionHasErrors('email');

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'new-secret-password',
        ])
            ->assertRedirect(route('client.requests.index'));
    }

    public function test_client_can_reset_password_from_notification_url_and_rendered_form_values(): void
    {
        Notification::fake();
        $user = $this->createClientUser([
            'email' => 'client-real-flow@example.com',
        ]);
        $resetUrl = null;

        $this->post(route('password.email'), [
            'email' => $user->email,
        ])
            ->assertSessionHas('status', 'Si un compte correspond à cette adresse, un lien de réinitialisation a été envoyé.');

        Notification::assertSentTo(
            $user,
            ResetPasswordNotification::class,
            function (ResetPasswordNotification $notification) use ($user, &$resetUrl): bool {
                $mail = $notification->toMail($user);
                $resetUrl = $mail->actionUrl;

                return is_string($resetUrl) && Str::contains($resetUrl, '/reinitialiser-mot-de-passe/');
            }
        );

        $formResponse = $this->get($resetUrl);
        $formResponse
            ->assertOk()
            ->assertSee($user->email);

        $html = $formResponse->getContent();

        preg_match('/name="token" value="([^"]+)"/', $html, $tokenMatches);
        preg_match('/name="email"[^>]*value="([^"]+)"/s', $html, $emailMatches);

        $this->assertNotEmpty($tokenMatches[1] ?? null);
        $this->assertSame($user->email, html_entity_decode($emailMatches[1] ?? '', ENT_QUOTES));

        $this->post(route('password.update'), [
            'token' => html_entity_decode($tokenMatches[1], ENT_QUOTES),
            'email' => html_entity_decode($emailMatches[1], ENT_QUOTES),
            'password' => 'NouveauPass123!',
            'password_confirmation' => 'NouveauPass123!',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Votre mot de passe a été réinitialisé. Vous pouvez maintenant vous connecter.');

        $this->assertTrue(Hash::check('NouveauPass123!', $user->fresh()->password));
    }

    public function test_password_confirmation_is_required_and_must_match(): void
    {
        $user = $this->createClientUser();
        $token = app('auth.password.broker')->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secret-password',
        ])
            ->assertSessionHasErrors('password');

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secret-password',
            'password_confirmation' => 'different-password',
        ])
            ->assertSessionHasErrors('password');
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = $this->createClientUser();
        $oldPasswordHash = $user->password;

        $this->post(route('password.update'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])
            ->assertSessionHasErrors('email');

        $this->assertSame($oldPasswordHash, $user->fresh()->password);
    }

    public function test_wrong_email_token_pair_is_rejected(): void
    {
        $user = $this->createClientUser([
            'email' => 'token-owner@example.com',
        ]);
        $otherUser = $this->createClientUser([
            'email' => 'wrong-email@example.com',
        ]);
        $token = app('auth.password.broker')->createToken($user);
        $oldPasswordHash = $otherUser->password;

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $otherUser->email,
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])
            ->assertSessionHasErrors('email');

        $this->assertSame($oldPasswordHash, $otherUser->fresh()->password);
    }

    public function test_reset_accepts_prefilled_email_with_extra_whitespace_without_changing_token(): void
    {
        $user = $this->createClientUser([
            'email' => 'client-trim-reset@example.com',
        ]);
        $token = app('auth.password.broker')->createToken($user);

        $this->get(route('password.reset', [
            'token' => $token,
            'email' => ' '.$user->email.' ',
        ]))
            ->assertOk()
            ->assertSee('value="'.$user->email.'"', false)
            ->assertSee('name="token" value="'.$token.'"', false);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => ' '.$user->email.' ',
            'password' => 'NouveauPass123!',
            'password_confirmation' => 'NouveauPass123!',
        ])
            ->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NouveauPass123!', $user->fresh()->password));
    }

    public function test_expired_token_is_rejected(): void
    {
        $user = $this->createClientUser();
        $token = app('auth.password.broker')->createToken($user);
        $oldPasswordHash = $user->password;

        $this->travel(61)->minutes();

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])
            ->assertSessionHasErrors('email');

        $this->assertSame($oldPasswordHash, $user->fresh()->password);
    }

    public function test_scrapyard_user_can_reset_password_and_login_to_scrapyard_area(): void
    {
        $user = $this->createScrapyardUserWithScrapyard([
            'email' => 'scrapyard-reset@example.com',
        ]);
        $token = app('auth.password.broker')->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])
            ->assertRedirect(route('login'));

        $user->refresh();

        $this->assertSame('scrapyard', $user->role);
        $this->assertTrue(Hash::check('new-secret-password', $user->password));

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'new-secret-password',
        ])
            ->assertRedirect(route('scrapyard.dashboard'));
    }

    public function test_existing_user_data_and_part_hold_requests_are_preserved_after_reset(): void
    {
        $user = $this->createClientUser([
            'name' => 'Ancien Client',
            'email' => 'ancien-client@example.com',
            'phone' => '0696123456',
        ]);
        $holdRequest = $this->createHoldRequestForClient($user);
        $token = app('auth.password.broker')->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])
            ->assertRedirect(route('login'));

        $user->refresh();

        $this->assertSame('Ancien Client', $user->name);
        $this->assertSame('ancien-client@example.com', $user->email);
        $this->assertSame('0696123456', $user->phone);
        $this->assertDatabaseHas('part_hold_requests', [
            'id' => $holdRequest->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    public function test_client_registration_still_works_after_password_reset_routes_are_added(): void
    {
        $this->post(route('client.register.store'), [
            'name' => 'Client Inscription',
            'email' => 'client-inscription@example.com',
            'phone' => '0696000000',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])
            ->assertRedirect(route('client.requests.index'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'client-inscription@example.com',
            'role' => 'client',
        ]);
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

    private function createScrapyardUserWithScrapyard(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'name' => 'Casse Test',
            'email' => 'casse-' . uniqid() . '@example.com',
            'phone' => '0596000000',
            'password' => Hash::make('password'),
        ], $attributes));
        $user->forceFill(['role' => 'scrapyard'])->save();

        Scrapyard::query()->create([
            'user_id' => $user->id,
            'name' => 'Casse Martinique',
            'slug' => 'casse-' . uniqid(),
            'city' => 'Fort-de-France',
            'is_active' => true,
        ]);

        return $user;
    }

    private function createHoldRequestForClient(User $client): PartHoldRequest
    {
        return PartHoldRequest::query()->create([
            'user_id' => $client->id,
            'part_id' => $this->createPublishedAvailablePart()->id,
            'status' => 'pending',
            'customer_message' => 'Je souhaite réserver cette pièce.',
        ]);
    }

    private function createPublishedAvailablePart(): Part
    {
        $scrapyardUser = $this->createScrapyardUserWithScrapyard();
        $vehicle = Vehicle::query()->create([
            'scrapyard_id' => $scrapyardUser->scrapyard->id,
            'brand' => 'Renault',
            'model' => 'Clio',
            'year' => 2018,
        ]);

        return Part::query()->create([
            'vehicle_id' => $vehicle->id,
            'name' => 'Alternateur',
            'category' => 'Électricité',
            'condition' => 'used_good',
            'status' => 'available',
            'price' => 120,
            'is_published' => true,
        ]);
    }
}
