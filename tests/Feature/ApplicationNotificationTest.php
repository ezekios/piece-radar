<?php

namespace Tests\Feature;

use App\Models\Part;
use App\Models\PartHoldRequest;
use App\Models\SavedPartSearch;
use App\Models\Scrapyard;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\ArrivalMatchingService;
use App\Services\ExpirePartHoldReservations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApplicationNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_matching_creates_notification_for_the_matching_client_only(): void
    {
        $matchingClient = $this->createClientUser([
            'name' => 'Client Matching Secret',
            'email' => 'client-matching-secret@example.com',
            'phone' => '0696000000',
        ]);
        $otherClient = $this->createClientUser([
            'name' => 'Client Hors Matching',
            'email' => 'client-hors-matching@example.com',
            'phone' => '0696000001',
        ]);
        [, $scrapyard] = $this->createScrapyardAccount('Casse Matching');

        $savedSearch = $this->createSavedSearch($matchingClient, [
            'part_name' => 'Amortisseur',
            'vehicle_brand' => 'Renault',
            'vehicle_model' => 'Clio 4',
        ]);
        $this->createSavedSearch($otherClient, [
            'part_name' => 'Alternateur',
            'vehicle_brand' => 'Peugeot',
            'vehicle_model' => '208',
        ]);

        $part = $this->createPart(
            $this->createVehicle($scrapyard, ['brand' => 'Renault', 'model' => 'Clio 4']),
            ['name' => 'Amortisseur avant gauche']
        );

        app(ArrivalMatchingService::class)->matchPart($part);

        $matchingClientNotification = $matchingClient->fresh()->notifications()->firstOrFail();

        $this->assertSame('matched', $savedSearch->fresh()->status);
        $this->assertSame('saved_part_search_matched', $matchingClientNotification->data['kind']);
        $this->assertSame('Correspondance trouvée', $matchingClientNotification->data['title']);
        $this->assertSame($part->id, $matchingClientNotification->data['matched_part_id']);
        $this->assertSame(route('pieces.show', $part), $matchingClientNotification->data['url']);
        $this->assertSame(0, $otherClient->fresh()->notifications()->count());
        $this->assertSame(0, PartHoldRequest::query()->count());
        $this->assertNotificationDataDoesNotExposeClientContact($matchingClientNotification->data, $matchingClient);
    }

    public function test_matching_an_already_matched_search_does_not_create_duplicate_notification(): void
    {
        $client = $this->createClientUser();
        [, $scrapyard] = $this->createScrapyardAccount('Casse Anti Doublon');
        $this->createSavedSearch($client, [
            'part_name' => 'Phare',
            'vehicle_brand' => 'Renault',
            'vehicle_model' => 'Clio',
        ]);
        $part = $this->createPart(
            $this->createVehicle($scrapyard, ['brand' => 'Renault', 'model' => 'Clio']),
            ['name' => 'Phare avant droit']
        );

        app(ArrivalMatchingService::class)->matchPart($part);
        app(ArrivalMatchingService::class)->matchPart($part);

        $this->assertSame(1, $client->fresh()->notifications()->count());
        $this->assertSame(1, SavedPartSearch::query()->where('status', 'matched')->count());
    }

    public function test_accepting_request_creates_application_notification_for_client(): void
    {
        Carbon::setTestNow('2026-08-31 10:00:00');
        $client = $this->createClientUser();
        $holdRequest = $this->createHoldRequest(client: $client);

        $this->post(route('scrapyard.requests.accept', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest));

        $notification = $client->fresh()
            ->notifications()
            ->get()
            ->firstWhere('data.kind', 'part_hold_request_accepted');

        $this->assertNotNull($notification);

        $this->assertSame('accepted', $holdRequest->fresh()->status);
        $this->assertTrue($holdRequest->fresh()->reserved_until->equalTo(now()->addHours(48)));
        $this->assertSame('Demande acceptée', $notification->data['title']);
        $this->assertSame(route('client.requests.show', $holdRequest), $notification->data['url']);
        $this->assertNotificationDataDoesNotExposeClientContact($notification->data, $client);
    }

    public function test_refusing_request_creates_application_notification_for_client(): void
    {
        $client = $this->createClientUser();
        $holdRequest = $this->createHoldRequest(client: $client);

        $this->post(route('scrapyard.requests.refuse', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest));

        $notification = $client->fresh()
            ->notifications()
            ->get()
            ->firstWhere('data.kind', 'part_hold_request_refused');

        $this->assertNotNull($notification);

        $this->assertSame('refused', $holdRequest->fresh()->status);
        $this->assertSame('Demande refusée', $notification->data['title']);
        $this->assertSame(route('client.requests.show', $holdRequest), $notification->data['url']);
        $this->assertNotificationDataDoesNotExposeClientContact($notification->data, $client);
    }

    public function test_expiring_request_creates_application_notification_for_client(): void
    {
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

        $expiredCount = app(ExpirePartHoldReservations::class)->handle();

        $notification = $client->fresh()
            ->notifications()
            ->get()
            ->firstWhere('data.kind', 'part_hold_request_expired');

        $this->assertNotNull($notification);

        $this->assertSame(1, $expiredCount);
        $this->assertSame('expired', $holdRequest->fresh()->status);
        $this->assertSame('Réservation expirée', $notification->data['title']);
        $this->assertSame(route('client.requests.show', $holdRequest), $notification->data['url']);
        $this->assertNotificationDataDoesNotExposeClientContact($notification->data, $client);
    }

    public function test_notification_center_shows_only_authenticated_user_notifications(): void
    {
        $client = $this->createClientUser();
        $otherClient = $this->createClientUser();
        [, $scrapyard] = $this->createScrapyardAccount('Casse Centre');
        $ownPart = $this->createPart(
            $this->createVehicle($scrapyard, ['brand' => 'Renault', 'model' => 'Clio']),
            ['name' => 'Capot client unique notification personnelle']
        );
        $otherPart = $this->createPart(
            $this->createVehicle($scrapyard, ['brand' => 'Peugeot', 'model' => '208']),
            ['name' => 'Aile autre unique notification autre client']
        );
        $this->createSavedSearch($client, [
            'part_name' => 'Capot client unique',
            'vehicle_brand' => 'Renault',
            'vehicle_model' => 'Clio',
        ]);
        $this->createSavedSearch($otherClient, [
            'part_name' => 'Aile autre unique',
            'vehicle_brand' => 'Peugeot',
            'vehicle_model' => '208',
        ]);

        app(ArrivalMatchingService::class)->matchPart($ownPart);
        app(ArrivalMatchingService::class)->matchPart($otherPart);

        $this->actingAs($client)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Capot client unique')
            ->assertDontSee('Aile autre unique');
    }

    public function test_visiting_notification_center_does_not_mark_notifications_as_read(): void
    {
        $client = $this->createClientUser();
        $notification = $this->createMatchingNotificationFor($client);

        $this->actingAs($client)
            ->get(route('notifications.index'))
            ->assertOk();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_opening_unread_notification_marks_it_as_read_and_redirects_to_resource(): void
    {
        $client = $this->createClientUser();
        $holdRequest = $this->createHoldRequest(client: $client);

        $this->post(route('scrapyard.requests.accept', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest));

        $notification = $client->fresh()
            ->notifications()
            ->get()
            ->firstWhere('data.kind', 'part_hold_request_accepted');

        $this->assertNotNull($notification);
        $this->assertNull($notification->read_at);

        $this->actingAs($client)
            ->get(route('notifications.open', $notification))
            ->assertRedirect(route('client.requests.show', $holdRequest));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_opening_already_read_notification_keeps_redirect_working(): void
    {
        $client = $this->createClientUser();
        $notification = $this->createMatchingNotificationFor($client);
        $notification->markAsRead();
        $readAt = $notification->fresh()->read_at;

        $this->actingAs($client)
            ->get(route('notifications.open', $notification))
            ->assertRedirect($notification->data['url']);

        $this->assertTrue($notification->fresh()->read_at->equalTo($readAt));
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $client = $this->createClientUser();
        $otherClient = $this->createClientUser();
        $notification = $this->createMatchingNotificationFor($otherClient);

        $this->actingAs($client)
            ->patch(route('notifications.mark-read', $notification))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_open_another_users_notification(): void
    {
        $client = $this->createClientUser();
        $otherClient = $this->createClientUser();
        $notification = $this->createMatchingNotificationFor($otherClient);

        $this->actingAs($client)
            ->get(route('notifications.open', $notification))
            ->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_user_can_delete_own_notification(): void
    {
        $client = $this->createClientUser();
        $notification = $this->createMatchingNotificationFor($client);

        $this->actingAs($client)
            ->delete(route('notifications.destroy', $notification))
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHas('success', 'Notification supprimée.');

        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_notification(): void
    {
        $client = $this->createClientUser();
        $otherClient = $this->createClientUser();
        $notification = $this->createMatchingNotificationFor($otherClient);

        $this->actingAs($client)
            ->delete(route('notifications.destroy', $notification))
            ->assertNotFound();

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
        ]);
    }

    public function test_deleting_one_notification_does_not_delete_others(): void
    {
        $client = $this->createClientUser();
        $deletedNotification = $this->createMatchingNotificationFor($client, 'Phare');
        $keptNotification = $this->createMatchingNotificationFor($client, 'Alternateur');

        $this->actingAs($client)
            ->delete(route('notifications.destroy', $deletedNotification))
            ->assertRedirect(route('notifications.index'));

        $this->assertDatabaseMissing('notifications', [
            'id' => $deletedNotification->id,
        ]);
        $this->assertDatabaseHas('notifications', [
            'id' => $keptNotification->id,
        ]);
    }

    public function test_user_can_mark_own_notifications_as_read(): void
    {
        $client = $this->createClientUser();
        $notification = $this->createMatchingNotificationFor($client);

        $this->actingAs($client)
            ->patch(route('notifications.mark-read', $notification))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);

        $secondNotification = $this->createMatchingNotificationFor($client, 'Alternateur');

        $this->actingAs($client)
            ->post(route('notifications.mark-all-read'))
            ->assertRedirect();

        $this->assertNotNull($secondNotification->fresh()->read_at);
    }

    public function test_notification_link_points_to_authorized_client_resource(): void
    {
        $owner = $this->createClientUser();
        $otherClient = $this->createClientUser();
        $holdRequest = $this->createHoldRequest(client: $owner);

        $this->post(route('scrapyard.requests.accept', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest));

        $notification = $owner->fresh()
            ->notifications()
            ->get()
            ->firstWhere('data.kind', 'part_hold_request_accepted');

        $this->assertNotNull($notification);

        $url = $notification->data['url'];

        $this->actingAs($owner)
            ->get($url)
            ->assertOk();

        $this->actingAs($otherClient)
            ->get($url)
            ->assertNotFound();
    }

    public function test_notifications_page_does_not_expose_client_contact(): void
    {
        $client = $this->createClientUser([
            'name' => 'Client Confidentiel',
            'email' => 'client-confidentiel@example.com',
            'phone' => '0696999999',
        ]);

        $this->createMatchingNotificationFor($client);

        $this->actingAs($client)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertDontSee('Client Confidentiel')
            ->assertDontSee('client-confidentiel@example.com')
            ->assertDontSee('0696999999');
    }

    private function createClientUser(array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'name' => 'Client Test',
            'email' => 'client-' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'phone' => '0696000000',
            'password' => Hash::make('password'),
        ], $attributes));

        $user->forceFill(['role' => 'client'])->save();

        return $user;
    }

    /**
     * @return array{0: User, 1: Scrapyard}
     */
    private function createScrapyardAccount(string $name, array $userAttributes = []): array
    {
        $user = User::factory()->create(array_merge([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '-', $name)) . '-' . uniqid() . '@example.com',
            'email_verified_at' => now(),
            'phone' => '0596000000',
            'password' => Hash::make('password'),
        ], $userAttributes));

        $user->forceFill(['role' => 'scrapyard'])->save();

        $scrapyard = Scrapyard::query()->create([
            'user_id' => $user->id,
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)) . '-' . uniqid(),
            'city' => 'Fort-de-France',
            'is_active' => true,
        ]);

        if (! $this->app['auth']->guard()->check()) {
            $this->actingAs($user);
        }

        return [$user, $scrapyard];
    }

    private function createVehicle(Scrapyard $scrapyard, array $attributes = []): Vehicle
    {
        return Vehicle::query()->create([
            'scrapyard_id' => $scrapyard->id,
            'brand' => $attributes['brand'] ?? 'Renault',
            'model' => $attributes['model'] ?? 'Clio',
            'year' => $attributes['year'] ?? 2018,
            'engine' => $attributes['engine'] ?? '1.5 dCi',
            'fuel' => $attributes['fuel'] ?? 'Gazole',
        ]);
    }

    private function createPart(Vehicle $vehicle, array $attributes = []): Part
    {
        return Part::query()->create([
            'vehicle_id' => $vehicle->id,
            'name' => $attributes['name'] ?? 'Alternateur',
            'category' => $attributes['category'] ?? 'Moteur',
            'condition' => $attributes['condition'] ?? 'used_good',
            'status' => $attributes['status'] ?? 'available',
            'price' => $attributes['price'] ?? 85,
            'is_published' => $attributes['is_published'] ?? true,
        ]);
    }

    private function createSavedSearch(User $client, array $attributes = []): SavedPartSearch
    {
        return SavedPartSearch::query()->create([
            'user_id' => $client->id,
            'vehicle_brand' => $attributes['vehicle_brand'] ?? 'Renault',
            'vehicle_model' => $attributes['vehicle_model'] ?? 'Clio',
            'vehicle_year' => $attributes['vehicle_year'] ?? null,
            'part_name' => $attributes['part_name'] ?? 'Alternateur',
            'part_category' => $attributes['part_category'] ?? null,
            'status' => $attributes['status'] ?? 'active',
            'matched_part_id' => $attributes['matched_part_id'] ?? null,
            'matched_at' => $attributes['matched_at'] ?? null,
        ]);
    }

    private function createHoldRequest(
        ?Scrapyard $scrapyard = null,
        ?User $client = null,
        string $status = 'pending',
    ): PartHoldRequest {
        $scrapyard ??= $this->createScrapyardAccount('Casse Demande')[1];
        $client ??= $this->createClientUser();

        return PartHoldRequest::query()->create([
            'user_id' => $client->id,
            'part_id' => $this->createPart($this->createVehicle($scrapyard))->id,
            'status' => $status,
            'customer_message' => 'Je souhaite réserver cette pièce.',
        ]);
    }

    private function createMatchingNotificationFor(User $client, string $partName = 'Phare'): \Illuminate\Notifications\DatabaseNotification
    {
        $existingNotificationIds = $client->notifications()->pluck('id')->all();

        [, $scrapyard] = $this->createScrapyardAccount('Casse Notification ' . uniqid());
        $this->createSavedSearch($client, [
            'part_name' => $partName,
            'vehicle_brand' => 'Renault',
            'vehicle_model' => 'Clio',
        ]);
        $part = $this->createPart(
            $this->createVehicle($scrapyard, ['brand' => 'Renault', 'model' => 'Clio']),
            ['name' => $partName . ' avant droit']
        );

        app(ArrivalMatchingService::class)->matchPart($part);

        return $client->fresh()
            ->notifications()
            ->whereNotIn('id', $existingNotificationIds)
            ->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertNotificationDataDoesNotExposeClientContact(array $data, User $client): void
    {
        $serializedData = json_encode($data, JSON_THROW_ON_ERROR);

        $this->assertStringNotContainsString($client->name, $serializedData);
        $this->assertStringNotContainsString($client->email, $serializedData);
        $this->assertStringNotContainsString((string) $client->phone, $serializedData);
    }
}
