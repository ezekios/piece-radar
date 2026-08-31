<?php

namespace Tests\Feature;

use App\Models\Part;
use App\Models\PartHoldRequest;
use App\Models\Scrapyard;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Auth\Events\Verified;
use App\Notifications\Auth\VerifyEmailNotification as VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_registration_creates_unverified_user_sends_notification_and_redirects_to_notice(): void
    {
        Notification::fake();

        $this->post(route('client.register.store'), [
            'name' => 'Client Verification',
            'email' => 'client-verification@example.com',
            'phone' => '0696000000',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])
            ->assertRedirect(route('verification.notice'));

        $user = User::query()->where('email', 'client-verification@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('client', $user->role);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_unverified_client_can_open_verification_notice(): void
    {
        $client = $this->createClientUser(verified: false);

        $this->actingAs($client)
            ->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('Vérifiez votre email')
            ->assertSee($client->email);
    }

    public function test_guest_cannot_open_verification_notice(): void
    {
        $this->get(route('verification.notice'))
            ->assertRedirect(route('login'));
    }

    public function test_scrapyard_cannot_use_client_verification_notice(): void
    {
        $scrapyard = $this->createScrapyardUserWithScrapyard([
            'email_verified_at' => null,
        ]);

        $this->actingAs($scrapyard)
            ->get(route('verification.notice'))
            ->assertForbidden();
    }

    public function test_unverified_client_can_resend_verification_notification(): void
    {
        Notification::fake();
        $client = $this->createClientUser(verified: false);

        $this->actingAs($client)
            ->post(route('verification.send'))
            ->assertRedirect()
            ->assertSessionHas('status', 'Un nouveau lien de vérification a été envoyé.');

        Notification::assertSentTo($client, VerifyEmail::class);
    }

    public function test_verification_resend_route_is_throttled(): void
    {
        $middleware = Route::getRoutes()
            ->getByName('verification.send')
            ->gatherMiddleware();

        $this->assertContains('throttle:6,1', $middleware);
    }

    public function test_valid_signed_link_verifies_email_and_dispatches_verified_event(): void
    {
        Notification::fake();
        $client = $this->createClientUser(verified: false);
        $verificationUrl = null;

        $this->actingAs($client)
            ->post(route('verification.send'));

        Notification::assertSentTo(
            $client,
            VerifyEmail::class,
            function (VerifyEmail $notification) use ($client, &$verificationUrl): bool {
                $verificationUrl = $notification->toMail($client)->actionUrl;

                return is_string($verificationUrl) && Str::contains($verificationUrl, '/verification-email/');
            }
        );

        Event::fake([Verified::class]);

        $this->actingAs($client)
            ->get($verificationUrl)
            ->assertRedirect(route('client.requests.index'))
            ->assertSessionHas('status', 'Votre adresse email a été vérifiée.');

        $this->assertNotNull($client->fresh()->email_verified_at);
        Event::assertDispatched(Verified::class);
    }

    public function test_invalid_or_modified_verification_link_is_refused(): void
    {
        $client = $this->createClientUser(verified: false);

        $invalidHashUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $client->id,
                'hash' => sha1('wrong-email@example.com'),
            ]
        );

        $this->actingAs($client)
            ->get($invalidHashUrl)
            ->assertForbidden();

        $this->assertNull($client->fresh()->email_verified_at);
    }

    public function test_wrong_user_cannot_verify_another_client_email(): void
    {
        $clientA = $this->createClientUser(verified: false);
        $clientB = $this->createClientUser(verified: false);

        $clientBUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $clientB->id,
                'hash' => sha1($clientB->email),
            ]
        );

        $this->actingAs($clientA)
            ->get($clientBUrl)
            ->assertForbidden();

        $this->assertNull($clientA->fresh()->email_verified_at);
        $this->assertNull($clientB->fresh()->email_verified_at);
    }

    public function test_client_with_wrong_hash_cannot_verify_email(): void
    {
        $client = $this->createClientUser(verified: false);

        $wrongHashUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $client->id,
                'hash' => sha1('modified-'.$client->email),
            ]
        );

        $this->actingAs($client)
            ->get($wrongHashUrl)
            ->assertForbidden();

        $this->assertNull($client->fresh()->email_verified_at);
    }

    public function test_unverified_client_is_redirected_to_notice_for_sensitive_client_routes(): void
    {
        $client = $this->createClientUser(verified: false);
        $part = $this->createPublishedAvailablePart();

        $this->actingAs($client)
            ->get(route('pieces.request', $part))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($client)
            ->post(route('pieces.request.store', $part), [
                'customer_message' => 'Je souhaite réserver cette pièce.',
            ])
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($client)
            ->get(route('client.requests.index'))
            ->assertRedirect(route('verification.notice'));

        $this->assertSame(0, PartHoldRequest::query()->count());
    }

    public function test_verified_client_can_access_sensitive_routes_and_create_request(): void
    {
        $client = $this->createClientUser(verified: true);
        $part = $this->createPublishedAvailablePart();

        $this->actingAs($client)
            ->get(route('pieces.request', $part))
            ->assertOk()
            ->assertSee('Demande de mise de côté');

        $this->actingAs($client)
            ->post(route('pieces.request.store', $part), [
                'customer_message' => 'Je souhaite réserver cette pièce.',
            ])
            ->assertRedirect(route('pieces.show', $part));

        $this->actingAs($client)
            ->get(route('client.requests.index'))
            ->assertOk();

        $this->assertDatabaseHas('part_hold_requests', [
            'user_id' => $client->id,
            'part_id' => $part->id,
            'status' => 'pending',
        ]);
    }

    public function test_unverified_client_login_falls_back_to_verification_notice(): void
    {
        $client = $this->createClientUser(verified: false);

        $this->post(route('login'), [
            'email' => $client->email,
            'password' => 'password',
        ])
            ->assertRedirect(route('verification.notice'));
    }

    public function test_verified_client_login_falls_back_to_my_requests(): void
    {
        $client = $this->createClientUser(verified: true);

        $this->post(route('login'), [
            'email' => $client->email,
            'password' => 'password',
        ])
            ->assertRedirect(route('client.requests.index'));
    }

    public function test_scrapyard_with_unverified_email_can_still_access_scrapyard_dashboard(): void
    {
        $scrapyard = $this->createScrapyardUserWithScrapyard([
            'email_verified_at' => null,
        ]);

        $this->actingAs($scrapyard)
            ->get(route('scrapyard.dashboard'))
            ->assertOk()
            ->assertSee('Tableau de bord casse');
    }

    public function test_existing_unverified_client_keeps_requests_and_recovers_them_after_verification(): void
    {
        $client = $this->createClientUser(verified: false);
        $holdRequest = $this->createHoldRequestForClient($client, 'Demande existante');

        $this->actingAs($client)
            ->get(route('client.requests.index'))
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($client)
            ->get(URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(60),
                [
                    'id' => $client->id,
                    'hash' => sha1($client->email),
                ]
            ))
            ->assertRedirect(route('client.requests.index'));

        $this->actingAs($client->fresh())
            ->get(route('client.requests.index'))
            ->assertOk()
            ->assertSee('Demande existante')
            ->assertViewHas('requests', function ($requests) use ($holdRequest) {
                return $requests->contains('id', $holdRequest->id);
            });
    }

    public function test_password_reset_for_unverified_client_still_works_then_requires_verification_for_client_area(): void
    {
        $client = $this->createClientUser(verified: false);
        $token = app('auth.password.broker')->createToken($client);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $client->email,
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])
            ->assertRedirect(route('login'));

        $this->post(route('login'), [
            'email' => $client->email,
            'password' => 'new-secret-password',
        ])
            ->assertRedirect(route('verification.notice'));
    }

    public function test_public_search_and_part_detail_remain_public(): void
    {
        $part = $this->createPublishedAvailablePart('Phare public');

        $this->get(route('client.parts.index'))
            ->assertOk();

        $this->get(route('pieces.show', $part))
            ->assertOk()
            ->assertSee('Phare public');
    }

    private function createClientUser(bool $verified = true, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'name' => 'Client Test',
            'email' => 'client-' . uniqid() . '@example.com',
            'phone' => '0696000000',
            'password' => Hash::make('password'),
            'email_verified_at' => $verified ? now() : null,
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

    private function createPublishedAvailablePart(string $name = 'Alternateur'): Part
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
            'name' => $name,
            'category' => 'Électricité',
            'condition' => 'used_good',
            'status' => 'available',
            'price' => 120,
            'is_published' => true,
        ]);
    }

    private function createHoldRequestForClient(User $client, string $partName): PartHoldRequest
    {
        return PartHoldRequest::query()->create([
            'user_id' => $client->id,
            'part_id' => $this->createPublishedAvailablePart($partName)->id,
            'status' => 'pending',
            'customer_message' => 'Je souhaite réserver cette pièce.',
        ]);
    }
}
