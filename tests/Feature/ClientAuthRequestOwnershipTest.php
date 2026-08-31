<?php

namespace Tests\Feature;

use App\Models\Part;
use App\Models\PartHoldRequest;
use App\Models\Scrapyard;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\Auth\VerifyEmailNotification as VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ClientAuthRequestOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_part_search_is_accessible_to_guest(): void
    {
        $this->get(route('client.parts.index'))
            ->assertOk()
            ->assertSee('Résultats de recherche');
    }

    public function test_public_available_published_part_detail_is_accessible_to_guest(): void
    {
        $part = $this->createPublishedAvailablePart('Phare avant');

        $this->get(route('pieces.show', $part))
            ->assertOk()
            ->assertSee('Phare avant');
    }

    public function test_guest_is_redirected_to_login_for_request_form_and_request_creation(): void
    {
        $part = $this->createPublishedAvailablePart();

        $this->get(route('pieces.request', $part))
            ->assertRedirect(route('login'));

        $this->post(route('pieces.request.store', $part), [
            'customer_message' => 'Je souhaite réserver cette pièce.',
        ])
            ->assertRedirect(route('login'));
    }

    public function test_scrapyard_user_cannot_open_or_submit_client_part_request(): void
    {
        $scrapyardUser = $this->createScrapyardUserWithScrapyard();
        $part = $this->createPublishedAvailablePart();

        $this->actingAs($scrapyardUser)
            ->get(route('pieces.request', $part))
            ->assertForbidden();

        $this->actingAs($scrapyardUser)
            ->post(route('pieces.request.store', $part), [
                'customer_message' => 'Tentative interdite.',
            ])
            ->assertForbidden();

        $this->assertSame(0, PartHoldRequest::query()->count());
    }

    public function test_registration_page_is_accessible_to_guest(): void
    {
        $this->get(route('client.register.create'))
            ->assertOk()
            ->assertSee('Créer un compte client');
    }

    public function test_client_can_register_and_is_authenticated(): void
    {
        Notification::fake();

        $this->post(route('client.register.store'), [
            'name' => 'Client Test',
            'email' => 'client-register@example.com',
            'phone' => '0696000000',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])
            ->assertRedirect(route('verification.notice'));

        $user = User::query()->where('email', 'client-register@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('client', $user->role);
        $this->assertNull($user->email_verified_at);
        $this->assertTrue(Hash::check('secret-password', $user->password));
        $this->assertNotSame('secret-password', $user->password);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_rejects_already_used_email(): void
    {
        User::factory()->create([
            'email' => 'already-used@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->post(route('client.register.store'), [
            'name' => 'Client Test',
            'email' => 'already-used@example.com',
            'phone' => '0696000000',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])
            ->assertSessionHasErrors('email');
    }

    public function test_registration_rejects_scrapyard_email_without_modifying_scrapyard_account(): void
    {
        $scrapyardUser = $this->createScrapyardUserWithScrapyard([
            'email' => 'casse-existing@example.com',
        ]);
        $originalPassword = $scrapyardUser->password;

        $this->post(route('client.register.store'), [
            'name' => 'Client Test',
            'email' => 'casse-existing@example.com',
            'phone' => '0696000000',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])
            ->assertSessionHasErrors('email');

        $scrapyardUser->refresh();
        $this->assertSame('scrapyard', $scrapyardUser->role);
        $this->assertSame($originalPassword, $scrapyardUser->password);
    }

    public function test_registration_requires_valid_password_confirmation(): void
    {
        $this->post(route('client.register.store'), [
            'name' => 'Client Test',
            'email' => 'client-confirmation@example.com',
            'phone' => '0696000000',
            'password' => 'secret-password',
        ])
            ->assertSessionHasErrors('password');

        $this->post(route('client.register.store'), [
            'name' => 'Client Test',
            'email' => 'client-confirmation@example.com',
            'phone' => '0696000000',
            'password' => 'secret-password',
            'password_confirmation' => 'different-password',
        ])
            ->assertSessionHasErrors('password');
    }

    public function test_client_can_login_with_fallback_to_my_requests(): void
    {
        $client = $this->createClientUser([
            'email' => 'client-login@example.com',
        ]);

        $this->post(route('login'), [
            'email' => $client->email,
            'password' => 'password',
        ])
            ->assertRedirect(route('client.requests.index'));

        $this->assertAuthenticatedAs($client);
    }

    public function test_wrong_password_is_rejected_for_client_login(): void
    {
        $client = $this->createClientUser();

        $this->post(route('login'), [
            'email' => $client->email,
            'password' => 'wrong-password',
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_scrapyard_login_keeps_scrapyard_fallback(): void
    {
        $scrapyardUser = $this->createScrapyardUserWithScrapyard();

        $this->post(route('login'), [
            'email' => $scrapyardUser->email,
            'password' => 'password',
        ])
            ->assertRedirect(route('scrapyard.dashboard'));
    }

    public function test_client_login_respects_intended_request_form_url(): void
    {
        $client = $this->createClientUser();
        $part = $this->createPublishedAvailablePart();

        $this->get(route('pieces.request', $part))
            ->assertRedirect(route('login'));

        $this->post(route('login'), [
            'email' => $client->email,
            'password' => 'password',
        ])
            ->assertRedirect(route('pieces.request', $part));
    }

    public function test_client_logout_works(): void
    {
        $client = $this->createClientUser();

        $this->actingAs($client)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_authenticated_client_can_create_pending_request_owned_by_authenticated_user(): void
    {
        $client = $this->createClientUser();
        $part = $this->createPublishedAvailablePart();

        $this->actingAs($client)
            ->post(route('pieces.request.store', $part), [
                'customer_message' => 'Je souhaite réserver cette pièce.',
            ])
            ->assertRedirect(route('pieces.show', $part))
            ->assertSessionMissing('client'.'_email');

        $holdRequest = PartHoldRequest::query()->firstOrFail();

        $this->assertSame($client->id, $holdRequest->user_id);
        $this->assertSame($part->id, $holdRequest->part_id);
        $this->assertSame('pending', $holdRequest->status);
        $this->assertSame('Je souhaite réserver cette pièce.', $holdRequest->customer_message);
    }

    public function test_spoofed_email_does_not_change_request_owner(): void
    {
        $client = $this->createClientUser([
            'email' => 'real-client@example.com',
        ]);
        $otherClient = $this->createClientUser([
            'email' => 'other-client@example.com',
        ]);
        $part = $this->createPublishedAvailablePart();

        $this->actingAs($client)
            ->post(route('pieces.request.store', $part), [
                'email' => $otherClient->email,
                'name' => 'Other Client',
                'phone' => '0696999999',
                'customer_message' => 'Tentative de spoof.',
            ])
            ->assertRedirect(route('pieces.show', $part));

        $holdRequest = PartHoldRequest::query()->firstOrFail();

        $this->assertSame($client->id, $holdRequest->user_id);
        $this->assertNotSame($otherClient->id, $holdRequest->user_id);
        $this->assertSame('Tentative de spoof.', $holdRequest->customer_message);
    }

    public function test_guest_is_redirected_to_login_for_my_requests(): void
    {
        $this->get(route('client.requests.index'))
            ->assertRedirect(route('login'));
    }

    public function test_client_sees_only_own_requests_in_index(): void
    {
        $clientA = $this->createClientUser(['name' => 'Client A']);
        $clientB = $this->createClientUser(['name' => 'Client B']);
        $requestA = $this->createHoldRequestForClient($clientA, 'Pièce Client A');
        $requestB = $this->createHoldRequestForClient($clientB, 'Pièce Client B');

        $response = $this->actingAs($clientA)
            ->get(route('client.requests.index'));

        $response
            ->assertOk()
            ->assertSee('Pièce Client A')
            ->assertDontSee('Pièce Client B')
            ->assertViewHas('requests', function ($requests) use ($requestA, $requestB) {
                return $requests->contains('id', $requestA->id)
                    && ! $requests->contains('id', $requestB->id);
            });
    }

    public function test_client_can_open_own_request_but_not_another_client_request(): void
    {
        $clientA = $this->createClientUser(['name' => 'Client A']);
        $clientB = $this->createClientUser(['name' => 'Client B']);
        $requestA = $this->createHoldRequestForClient($clientA, 'Pièce Client A');
        $requestB = $this->createHoldRequestForClient($clientB, 'Pièce Client B');

        $this->actingAs($clientA)
            ->get(route('client.requests.show', $requestA))
            ->assertOk()
            ->assertSee('Pièce Client A');

        $this->actingAs($clientA)
            ->get(route('client.requests.show', $requestB))
            ->assertNotFound();
    }

    public function test_scrapyard_user_cannot_access_my_requests(): void
    {
        $scrapyardUser = $this->createScrapyardUserWithScrapyard();

        $this->actingAs($scrapyardUser)
            ->get(route('client.requests.index'))
            ->assertForbidden();
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

    private function createPublishedAvailablePart(string $name = 'Alternateur'): Part
    {
        $scrapyardUser = $this->createScrapyardUserWithScrapyard();
        $scrapyard = $scrapyardUser->scrapyard;
        $vehicle = Vehicle::query()->create([
            'scrapyard_id' => $scrapyard->id,
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
