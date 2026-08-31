<?php

namespace Tests\Feature;

use App\Models\Part;
use App\Models\PartHoldRequest;
use App\Models\Scrapyard;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\PartHoldRequests\NewPartHoldRequestNotification;
use App\Notifications\PartHoldRequests\PartHoldRequestAcceptedNotification;
use App\Notifications\PartHoldRequests\PartHoldRequestCancelledNotification;
use App\Notifications\PartHoldRequests\PartHoldRequestCompletedNotification;
use App\Notifications\PartHoldRequests\PartHoldRequestExpiredNotification;
use App\Notifications\PartHoldRequests\PartHoldRequestRefusedNotification;
use App\Services\ExpirePartHoldReservations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RequestEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_creating_hold_request_notifies_the_right_scrapyard_only(): void
    {
        Notification::fake();

        [, $scrapyard] = $this->createScrapyardAccount('Casse A', ['email' => 'atelier-casse-a@example.com']);
        [, $otherScrapyard] = $this->createScrapyardAccount('Casse B', ['email' => 'atelier-casse-b@example.com']);
        $client = $this->createClientUser([
            'name' => 'Client Secret',
            'email' => 'client-secret@example.com',
            'phone' => '0696000000',
        ]);
        $part = $this->createPart($this->createVehicle($scrapyard), [
            'name' => 'Phare notification',
            'reference' => 'REF-NOTIF',
        ]);

        $this->actingAs($client)
            ->post(route('pieces.request.store', $part), [
                'customer_message' => 'Je souhaite réserver cette pièce.',
            ])
            ->assertRedirect(route('pieces.show', $part));

        $holdRequest = PartHoldRequest::query()->firstOrFail();

        $this->assertSame('atelier-casse-a@example.com', $scrapyard->routeNotificationForMail());
        Notification::assertSentTo(
            $scrapyard,
            NewPartHoldRequestNotification::class,
            function (NewPartHoldRequestNotification $notification) use ($scrapyard, $client, $part, $holdRequest): bool {
                $mail = $notification->toMail($scrapyard);
                $content = $this->mailContent($mail);

                $this->assertSame('Nouvelle demande de mise de côté', $mail->subject);
                $this->assertStringContainsString('Une nouvelle demande de mise de côté a été reçue', $content);
                $this->assertStringContainsString($part->name, $content);
                $this->assertStringContainsString('REF-NOTIF', $content);
                $this->assertStringContainsString('Les coordonnées du client seront disponibles uniquement après acceptation.', $content);
                $this->assertStringNotContainsString($client->email, $content);
                $this->assertStringNotContainsString($client->phone, $content);
                $this->assertSame(route('scrapyard.requests.show', $holdRequest), $mail->actionUrl);

                return true;
            },
        );
        Notification::assertNotSentTo($otherScrapyard, NewPartHoldRequestNotification::class);
    }

    public function test_scrapyard_mail_route_falls_back_to_user_email(): void
    {
        [$user, $scrapyard] = $this->createScrapyardAccount('Casse Sans Email');

        $this->assertNull($scrapyard->email);
        $this->assertSame($user->email, $scrapyard->routeNotificationForMail());
    }

    public function test_accepting_request_notifies_client_with_48h_and_displayed_reserved_until(): void
    {
        Notification::fake();
        Carbon::setTestNow('2026-08-31 10:00:00');

        $client = $this->createClientUser();
        $holdRequest = $this->createHoldRequest(client: $client);

        $this->post(route('scrapyard.requests.accept', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest));

        Notification::assertSentTo(
            $client,
            PartHoldRequestAcceptedNotification::class,
            function (PartHoldRequestAcceptedNotification $notification) use ($client, $holdRequest): bool {
                $mail = $notification->toMail($client);
                $content = $this->mailContent($mail);

                $this->assertSame('Votre demande a été acceptée', $mail->subject);
                $this->assertStringContainsString('Votre pièce est réservée pendant 48 heures.', $content);
                $this->assertStringContainsString('Expiration de la réservation : 02/09/2026 à 06:00', $content);
                $this->assertSame(route('client.requests.show', $holdRequest), $mail->actionUrl);

                return true;
            },
        );
        $this->assertTrue($holdRequest->fresh()->reserved_until->equalTo(now()->addHours(48)));
    }

    public function test_refusing_request_notifies_client(): void
    {
        Notification::fake();
        $client = $this->createClientUser();
        $holdRequest = $this->createHoldRequest(client: $client);

        $this->post(route('scrapyard.requests.refuse', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest));

        Notification::assertSentTo(
            $client,
            PartHoldRequestRefusedNotification::class,
            fn (PartHoldRequestRefusedNotification $notification): bool => $notification->toMail($client)->actionUrl === route('client.requests.show', $holdRequest),
        );
    }

    public function test_cancelling_accepted_request_notifies_client(): void
    {
        Notification::fake();
        $client = $this->createClientUser();
        $holdRequest = $this->createHoldRequest(client: $client, status: 'accepted');
        $holdRequest->part->update(['status' => 'reserved']);

        $this->post(route('scrapyard.requests.cancel', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest));

        Notification::assertSentTo($client, PartHoldRequestCancelledNotification::class);
    }

    public function test_completing_accepted_request_notifies_client(): void
    {
        Notification::fake();
        $client = $this->createClientUser();
        $holdRequest = $this->createHoldRequest(client: $client, status: 'accepted');
        $holdRequest->part->update(['status' => 'reserved']);

        $this->post(route('scrapyard.requests.complete', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest));

        Notification::assertSentTo($client, PartHoldRequestCompletedNotification::class);
    }

    public function test_expiring_request_notifies_client_once(): void
    {
        Notification::fake();
        Carbon::setTestNow('2026-08-31 10:00:00');

        $client = $this->createClientUser();
        $holdRequest = $this->createHoldRequest(client: $client, status: 'accepted');
        $holdRequest->update([
            'reserved_until' => now()->subMinute(),
        ]);
        $holdRequest->part->update([
            'status' => 'reserved',
            'is_published' => true,
        ]);

        app(ExpirePartHoldReservations::class)->handle();
        app(ExpirePartHoldReservations::class)->handle();

        Notification::assertSentTo($client, PartHoldRequestExpiredNotification::class);
        $this->assertCount(1, Notification::sent($client, PartHoldRequestExpiredNotification::class));
        $this->assertSame('expired', $holdRequest->fresh()->status);
    }

    public function test_double_acceptation_does_not_send_duplicate_accepted_notifications(): void
    {
        Notification::fake();

        $winningClient = $this->createClientUser(['email' => 'winner@example.com']);
        $losingClient = $this->createClientUser(['email' => 'loser@example.com']);
        $winningRequest = $this->createHoldRequest(client: $winningClient);
        $losingRequest = $this->createHoldRequestForPart($winningRequest->part, $losingClient);

        $this->post(route('scrapyard.requests.accept', $winningRequest))
            ->assertRedirect(route('scrapyard.requests.show', $winningRequest));

        $this->post(route('scrapyard.requests.accept', $losingRequest))
            ->assertRedirect(route('scrapyard.requests.show', $losingRequest));

        $this->assertCount(1, Notification::sent($winningClient, PartHoldRequestAcceptedNotification::class));
        $this->assertCount(0, Notification::sent($losingClient, PartHoldRequestAcceptedNotification::class));
        $this->assertCount(1, Notification::sent($losingClient, PartHoldRequestRefusedNotification::class));
        $this->assertSame('accepted', $winningRequest->fresh()->status);
        $this->assertSame('refused', $losingRequest->fresh()->status);
    }

    public function test_invalid_status_transition_does_not_send_notification(): void
    {
        Notification::fake();

        $client = $this->createClientUser();
        $holdRequest = $this->createHoldRequest(client: $client, status: 'refused');

        $this->post(route('scrapyard.requests.refuse', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest));

        $this->post(route('scrapyard.requests.accept', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest));

        Notification::assertNothingSentTo($client);
    }

    public function test_scrapyard_cannot_trigger_notification_for_another_scrapyard_request(): void
    {
        Notification::fake();

        [$userA] = $this->createScrapyardAccount('Casse A');
        [, $scrapyardB] = $this->createScrapyardAccount('Casse B');
        $clientB = $this->createClientUser(['email' => 'client-b@example.com']);
        $holdRequestB = $this->createHoldRequest(scrapyard: $scrapyardB, client: $clientB);

        $this->actingAs($userA)
            ->post(route('scrapyard.requests.accept', $holdRequestB))
            ->assertNotFound();

        Notification::assertNothingSentTo($clientB);
        $this->assertSame('pending', $holdRequestB->fresh()->status);
    }

    private function mailContent(MailMessage $mail): string
    {
        return implode("\n", [
            ...array_map('strval', $mail->introLines),
            ...array_map('strval', $mail->outroLines),
        ]);
    }

    /**
     * @return array{0: User, 1: Scrapyard}
     */
    private function createScrapyardAccount(string $name = 'Casse Test', array $scrapyardAttributes = []): array
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '-', $name)) . '-' . uniqid() . '@example.com',
            'phone' => '0596000000',
            'password' => Hash::make('password'),
        ]);
        $user->forceFill(['role' => 'scrapyard'])->save();

        $scrapyard = Scrapyard::query()->create(array_merge([
            'user_id' => $user->id,
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)) . '-' . uniqid(),
            'city' => 'Fort-de-France',
            'is_active' => true,
        ], $scrapyardAttributes));

        if (! $this->app['auth']->guard()->check()) {
            $this->actingAs($user);
        }

        return [$user, $scrapyard];
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

    private function createVehicle(Scrapyard $scrapyard): Vehicle
    {
        return Vehicle::query()->create([
            'scrapyard_id' => $scrapyard->id,
            'brand' => 'Renault',
            'model' => 'Clio',
            'year' => 2018,
            'engine' => '1.5 dCi',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createPart(Vehicle $vehicle, array $attributes = []): Part
    {
        return Part::query()->create([
            'vehicle_id' => $vehicle->id,
            'name' => $attributes['name'] ?? 'Phare avant droit',
            'category' => 'Optique',
            'reference' => $attributes['reference'] ?? null,
            'condition' => 'used_good',
            'status' => $attributes['status'] ?? 'available',
            'price' => 85,
            'is_published' => $attributes['is_published'] ?? true,
        ]);
    }

    private function createHoldRequest(
        ?Scrapyard $scrapyard = null,
        ?User $client = null,
        string $status = 'pending',
    ): PartHoldRequest {
        $scrapyard ??= $this->createScrapyardAccount()[1];
        $client ??= $this->createClientUser();

        return PartHoldRequest::query()->create([
            'user_id' => $client->id,
            'part_id' => $this->createPart($this->createVehicle($scrapyard))->id,
            'status' => $status,
            'customer_message' => 'Je souhaite réserver cette pièce.',
        ]);
    }

    private function createHoldRequestForPart(Part $part, User $client, string $status = 'pending'): PartHoldRequest
    {
        return PartHoldRequest::query()->create([
            'user_id' => $client->id,
            'part_id' => $part->id,
            'status' => $status,
            'customer_message' => 'Je souhaite réserver cette pièce.',
        ]);
    }
}
