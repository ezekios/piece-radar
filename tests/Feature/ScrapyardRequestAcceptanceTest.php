<?php

namespace Tests\Feature;

use App\Models\Part;
use App\Models\PartHoldRequest;
use App\Models\Scrapyard;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ScrapyardRequestAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_application_keeps_utc_internally_and_uses_martinique_for_display(): void
    {
        $this->assertSame('UTC', config('app.timezone'));
        $this->assertSame('UTC', date_default_timezone_get());
        $this->assertSame('UTC', now()->timezoneName);
        $this->assertSame('fr', config('app.locale'));
        $this->assertSame('America/Martinique', config('app.display_timezone'));
    }

    public function test_pending_request_acceptance_confirmation_page_is_accessible(): void
    {
        $holdRequest = $this->createHoldRequest();

        $response = $this->get(route('scrapyard.requests.accept.confirm', $holdRequest));

        $response
            ->assertOk()
            ->assertSee('Confirmer l’acceptation')
            ->assertSee('Vous êtes sur le point d’accepter cette demande.');
    }

    public function test_opening_acceptance_confirmation_does_not_change_request_status(): void
    {
        $holdRequest = $this->createHoldRequest();

        $this->get(route('scrapyard.requests.accept.confirm', $holdRequest))
            ->assertOk();

        $this->assertSame('pending', $holdRequest->fresh()->status);
        $this->assertNull($holdRequest->fresh()->reserved_until);
    }

    public function test_scrapyard_cannot_confirm_request_from_another_scrapyard(): void
    {
        $this->createScrapyard();
        $otherScrapyard = $this->createScrapyard('other-scrapyard');
        $holdRequest = $this->createHoldRequest($otherScrapyard);

        $this->get(route('scrapyard.requests.accept.confirm', $holdRequest))
            ->assertNotFound();

        $this->post(route('scrapyard.requests.accept', $holdRequest))
            ->assertNotFound();

        $this->assertSame('pending', $holdRequest->fresh()->status);
    }

    public function test_final_confirmation_accepts_pending_request(): void
    {
        Carbon::setTestNow('2026-08-31 10:00:00');
        $holdRequest = $this->createHoldRequest();

        $this->post(route('scrapyard.requests.accept', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest))
            ->assertSessionHas('success', 'La demande a été acceptée. La pièce est maintenant mise de côté.');

        $this->assertSame('accepted', $holdRequest->fresh()->status);
        $this->assertTrue($holdRequest->fresh()->reserved_until->equalTo(now()->addHours(48)));
        $this->assertSame(48.0, now()->diffInHours($holdRequest->fresh()->reserved_until));
        $this->assertSame('reserved', $holdRequest->part->fresh()->status);
    }

    public function test_reserved_until_is_displayed_in_martinique_timezone_with_french_remaining_time(): void
    {
        Carbon::setTestNow('2026-08-31 13:22:00');
        $holdRequest = $this->createHoldRequest(status: 'accepted');
        $holdRequest->update([
            'reserved_until' => Carbon::parse('2026-09-01 16:53:00', 'UTC'),
        ]);

        $this->get(route('scrapyard.requests.show', $holdRequest))
            ->assertOk()
            ->assertSee('Pièce réservée jusqu’au 01/09/2026 à 12:53')
            ->assertSee('Temps restant : 1 jour et 3 heures.')
            ->assertDontSee('from now');
    }

    public function test_multiple_pending_requests_can_exist_for_same_part_before_acceptance(): void
    {
        $firstRequest = $this->createHoldRequest();
        $part = $firstRequest->part;

        $secondRequest = $this->createHoldRequestForPart($part);
        $thirdRequest = $this->createHoldRequestForPart($part);

        $this->assertSame('pending', $firstRequest->fresh()->status);
        $this->assertSame('pending', $secondRequest->fresh()->status);
        $this->assertSame('pending', $thirdRequest->fresh()->status);
        $this->assertSame(3, PartHoldRequest::query()
            ->where('part_id', $part->id)
            ->where('status', 'pending')
            ->count());
    }

    public function test_accepting_one_request_refuses_other_pending_requests_for_same_part(): void
    {
        Carbon::setTestNow('2026-08-31 10:00:00');
        $acceptedClient = User::factory()->create([
            'name' => 'Client Gagnant',
            'email' => 'client-gagnant@example.com',
            'phone' => '0696123456',
        ]);
        $refusedClient = User::factory()->create([
            'name' => 'Client Refusé Auto',
            'email' => 'client-refuse-auto@example.com',
            'phone' => '0696654321',
        ]);
        $winningRequest = $this->createHoldRequest(client: $acceptedClient);
        $part = $winningRequest->part;
        $secondRequest = $this->createHoldRequestForPart($part, $refusedClient);
        $thirdRequest = $this->createHoldRequestForPart($part);

        $this->post(route('scrapyard.requests.accept', $winningRequest))
            ->assertRedirect(route('scrapyard.requests.show', $winningRequest))
            ->assertSessionHas('success', 'La demande a été acceptée. La pièce est maintenant mise de côté.');

        $this->assertSame('accepted', $winningRequest->fresh()->status);
        $this->assertTrue($winningRequest->fresh()->reserved_until->equalTo(now()->addHours(48)));
        $this->assertSame(48.0, now()->diffInHours($winningRequest->fresh()->reserved_until));
        $this->assertSame('reserved', $part->fresh()->status);
        $this->assertSame('refused', $secondRequest->fresh()->status);
        $this->assertSame('refused', $thirdRequest->fresh()->status);

        $this->get(route('scrapyard.requests.show', $winningRequest))
            ->assertOk()
            ->assertSee('Client Gagnant')
            ->assertSee('client-gagnant@example.com')
            ->assertSee('0696123456');

        $this->get(route('scrapyard.requests.show', $secondRequest))
            ->assertOk()
            ->assertDontSee('Client Refusé Auto')
            ->assertDontSee('client-refuse-auto@example.com')
            ->assertDontSee('0696654321');
    }

    public function test_losing_request_cannot_be_accepted_after_same_part_was_reserved(): void
    {
        $winningRequest = $this->createHoldRequest();
        $part = $winningRequest->part;
        $losingRequest = $this->createHoldRequestForPart($part);

        $this->post(route('scrapyard.requests.accept', $winningRequest))
            ->assertRedirect(route('scrapyard.requests.show', $winningRequest));

        $this->post(route('scrapyard.requests.accept', $losingRequest))
            ->assertRedirect(route('scrapyard.requests.show', $losingRequest))
            ->assertSessionHas('error', 'Cette demande ne peut plus être traitée.');

        $this->assertSame('accepted', $winningRequest->fresh()->status);
        $this->assertSame('refused', $losingRequest->fresh()->status);
        $this->assertSame('reserved', $part->fresh()->status);
        $this->assertSame(1, PartHoldRequest::query()
            ->where('part_id', $part->id)
            ->where('status', 'accepted')
            ->count());
    }

    public function test_automatically_refused_request_cannot_become_accepted(): void
    {
        $winningRequest = $this->createHoldRequest();
        $part = $winningRequest->part;
        $automaticallyRefusedRequest = $this->createHoldRequestForPart($part);

        $this->post(route('scrapyard.requests.accept', $winningRequest))
            ->assertRedirect(route('scrapyard.requests.show', $winningRequest));

        $this->assertSame('refused', $automaticallyRefusedRequest->fresh()->status);

        $this->post(route('scrapyard.requests.accept', $automaticallyRefusedRequest))
            ->assertRedirect(route('scrapyard.requests.show', $automaticallyRefusedRequest))
            ->assertSessionHas('error', 'Cette demande ne peut plus être traitée.');

        $this->assertSame('refused', $automaticallyRefusedRequest->fresh()->status);
        $this->assertSame('accepted', $winningRequest->fresh()->status);
    }

    public function test_reserved_part_blocks_new_acceptance(): void
    {
        $holdRequest = $this->createHoldRequest();
        $holdRequest->part->update([
            'status' => 'reserved',
        ]);

        $this->post(route('scrapyard.requests.accept', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest))
            ->assertSessionHas('error', 'La pièce n’est plus disponible pour une mise de côté.');

        $this->assertSame('pending', $holdRequest->fresh()->status);
        $this->assertSame('reserved', $holdRequest->part->fresh()->status);
        $this->assertNull($holdRequest->fresh()->reserved_until);
    }

    public function test_unpublished_available_part_can_still_accept_existing_request(): void
    {
        $holdRequest = $this->createHoldRequest();
        $holdRequest->part->update([
            'status' => 'available',
            'is_published' => false,
        ]);

        $this->post(route('scrapyard.requests.accept', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest))
            ->assertSessionHas('success', 'La demande a été acceptée. La pièce est maintenant mise de côté.');

        $this->assertSame('accepted', $holdRequest->fresh()->status);
        $this->assertSame('reserved', $holdRequest->part->fresh()->status);
        $this->assertFalse($holdRequest->part->fresh()->is_published);
    }

    public function test_existing_accepted_request_for_same_available_part_blocks_new_acceptance(): void
    {
        $acceptedRequest = $this->createHoldRequest(status: 'accepted');
        $part = $acceptedRequest->part;
        $part->update([
            'status' => 'available',
        ]);
        $pendingRequest = $this->createHoldRequestForPart($part);

        $this->post(route('scrapyard.requests.accept', $pendingRequest))
            ->assertRedirect(route('scrapyard.requests.show', $pendingRequest))
            ->assertSessionHas('error', 'Une autre demande a déjà été acceptée pour cette pièce.');

        $this->assertSame('accepted', $acceptedRequest->fresh()->status);
        $this->assertSame('pending', $pendingRequest->fresh()->status);
        $this->assertSame('available', $part->fresh()->status);
    }

    public function test_non_pending_requests_cannot_be_accepted_again(): void
    {
        $scrapyard = $this->createScrapyard();

        foreach (['accepted', 'refused', 'cancelled', 'completed', 'expired'] as $status) {
            $holdRequest = $this->createHoldRequest(scrapyard: $scrapyard, status: $status);

            $this->post(route('scrapyard.requests.accept', $holdRequest))
                ->assertRedirect(route('scrapyard.requests.show', $holdRequest))
                ->assertSessionHas('error', 'Cette demande ne peut plus être traitée.');

            $this->assertSame($status, $holdRequest->fresh()->status);
        }
    }

    public function test_client_contact_is_not_exposed_on_acceptance_confirmation_page(): void
    {
        $client = User::factory()->create([
            'name' => 'Client Secret Radar',
            'email' => 'client-secret@example.com',
            'phone' => '0696000000',
        ]);
        $holdRequest = $this->createHoldRequest(client: $client);

        $response = $this->get(route('scrapyard.requests.accept.confirm', $holdRequest));

        $response
            ->assertOk()
            ->assertDontSee('Client Secret Radar')
            ->assertDontSee('client-secret@example.com')
            ->assertDontSee('0696000000');
    }

    public function test_client_contact_is_hidden_for_pending_request_on_scrapyard_pages(): void
    {
        $client = User::factory()->create([
            'name' => 'Pending Client Secret',
            'email' => 'pending-client-secret@example.com',
            'phone' => '0696111111',
        ]);
        $holdRequest = $this->createHoldRequest(client: $client);

        $this->get(route('scrapyard.requests.show', $holdRequest))
            ->assertOk()
            ->assertSee('Les coordonnées du client seront disponibles après acceptation de la demande.')
            ->assertDontSee('Pending Client Secret')
            ->assertDontSee('pending-client-secret@example.com')
            ->assertDontSee('0696111111');

        $this->get(route('scrapyard.requests.index'))
            ->assertOk()
            ->assertSee('Demande client')
            ->assertDontSee('Pending Client Secret')
            ->assertDontSee('pending-client-secret@example.com')
            ->assertDontSee('0696111111');

        $this->get(route('scrapyard.dashboard'))
            ->assertOk()
            ->assertSee('Demande client')
            ->assertDontSee('Pending Client Secret')
            ->assertDontSee('pending-client-secret@example.com')
            ->assertDontSee('0696111111');
    }

    public function test_client_contact_is_hidden_for_refused_request_on_scrapyard_pages(): void
    {
        $client = User::factory()->create([
            'name' => 'Refused Client Secret',
            'email' => 'refused-client-secret@example.com',
            'phone' => '0696222222',
        ]);
        $holdRequest = $this->createHoldRequest(client: $client, status: 'refused');

        $this->get(route('scrapyard.requests.show', $holdRequest))
            ->assertOk()
            ->assertSee('Les coordonnées du client seront disponibles après acceptation de la demande.')
            ->assertDontSee('Refused Client Secret')
            ->assertDontSee('refused-client-secret@example.com')
            ->assertDontSee('0696222222');

        $this->get(route('scrapyard.requests.index', ['status' => 'refused']))
            ->assertOk()
            ->assertSee('Demande client')
            ->assertDontSee('Refused Client Secret')
            ->assertDontSee('refused-client-secret@example.com')
            ->assertDontSee('0696222222');

        $this->get(route('scrapyard.dashboard'))
            ->assertOk()
            ->assertSee('Demande client')
            ->assertDontSee('Refused Client Secret')
            ->assertDontSee('refused-client-secret@example.com')
            ->assertDontSee('0696222222');
    }

    public function test_client_contact_is_hidden_for_cancelled_request_on_scrapyard_pages(): void
    {
        $client = User::factory()->create([
            'name' => 'Cancelled Client Secret',
            'email' => 'cancelled-client-secret@example.com',
            'phone' => '0696444444',
        ]);
        $holdRequest = $this->createHoldRequest(client: $client, status: 'cancelled');

        $this->get(route('scrapyard.requests.show', $holdRequest))
            ->assertOk()
            ->assertDontSee('Cancelled Client Secret')
            ->assertDontSee('cancelled-client-secret@example.com')
            ->assertDontSee('0696444444');

        $this->get(route('scrapyard.requests.index', ['status' => 'cancelled']))
            ->assertOk()
            ->assertDontSee('Cancelled Client Secret')
            ->assertDontSee('cancelled-client-secret@example.com')
            ->assertDontSee('0696444444');
    }

    public function test_client_contact_is_visible_for_accepted_request_on_scrapyard_request_pages(): void
    {
        $client = User::factory()->create([
            'name' => 'Accepted Client Visible',
            'email' => 'accepted-client-visible@example.com',
            'phone' => '0696333333',
        ]);
        $holdRequest = $this->createHoldRequest(client: $client, status: 'accepted');

        $this->get(route('scrapyard.requests.show', $holdRequest))
            ->assertOk()
            ->assertSee('Accepted Client Visible')
            ->assertSee('accepted-client-visible@example.com')
            ->assertSee('0696333333');

        $this->get(route('scrapyard.requests.index', ['status' => 'accepted']))
            ->assertOk()
            ->assertSee('Accepted Client Visible')
            ->assertSee('accepted-client-visible@example.com')
            ->assertSee('0696333333');
    }

    public function test_client_contact_is_visible_for_completed_request_on_scrapyard_request_pages(): void
    {
        $client = User::factory()->create([
            'name' => 'Completed Client Visible',
            'email' => 'completed-client-visible@example.com',
            'phone' => '0696555555',
        ]);
        $holdRequest = $this->createHoldRequest(client: $client, status: 'completed');

        $this->get(route('scrapyard.requests.show', $holdRequest))
            ->assertOk()
            ->assertSee('Completed Client Visible')
            ->assertSee('completed-client-visible@example.com')
            ->assertSee('0696555555');

        $this->get(route('scrapyard.requests.index', ['status' => 'completed']))
            ->assertOk()
            ->assertSee('Completed Client Visible')
            ->assertSee('completed-client-visible@example.com')
            ->assertSee('0696555555');
    }

    public function test_client_contact_is_hidden_for_expired_request_on_scrapyard_pages(): void
    {
        $client = User::factory()->create([
            'name' => 'Expired Client Secret',
            'email' => 'expired-client-secret@example.com',
            'phone' => '0696666666',
        ]);
        $holdRequest = $this->createHoldRequest(client: $client, status: 'expired');

        $this->get(route('scrapyard.requests.show', $holdRequest))
            ->assertOk()
            ->assertDontSee('Expired Client Secret')
            ->assertDontSee('expired-client-secret@example.com')
            ->assertDontSee('0696666666');

        $this->get(route('scrapyard.requests.index', ['status' => 'expired']))
            ->assertOk()
            ->assertSee('Expirée')
            ->assertDontSee('Expired Client Secret')
            ->assertDontSee('expired-client-secret@example.com')
            ->assertDontSee('0696666666');
    }

    public function test_refusing_pending_request_still_works(): void
    {
        $holdRequest = $this->createHoldRequest();

        $this->post(route('scrapyard.requests.refuse', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest))
            ->assertSessionHas('success', 'La demande a été refusée.');

        $this->assertSame('refused', $holdRequest->fresh()->status);
    }

    public function test_accepted_request_can_be_completed(): void
    {
        $client = User::factory()->create([
            'name' => 'Completed Lifecycle Client',
            'email' => 'completed-lifecycle@example.com',
            'phone' => '0696777777',
        ]);
        $holdRequest = $this->createHoldRequest(client: $client, status: 'accepted');
        $holdRequest->part->update([
            'status' => 'reserved',
            'is_published' => true,
        ]);

        $this->post(route('scrapyard.requests.complete', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest))
            ->assertSessionHas('success', 'La demande a été terminée.');

        $this->assertSame('completed', $holdRequest->fresh()->status);
        $this->assertNotNull($holdRequest->fresh()->handled_at);
        $this->assertNull($holdRequest->fresh()->reserved_until);
        $this->assertSame('sold', $holdRequest->part->fresh()->status);
        $this->assertFalse($holdRequest->part->fresh()->is_published);

        $this->get(route('scrapyard.requests.show', $holdRequest))
            ->assertOk()
            ->assertSee('Completed Lifecycle Client')
            ->assertSee('completed-lifecycle@example.com')
            ->assertSee('0696777777');
    }

    public function test_completing_request_does_not_make_part_available(): void
    {
        $holdRequest = $this->createHoldRequest(status: 'accepted');
        $holdRequest->part->update([
            'status' => 'reserved',
            'is_published' => true,
        ]);

        $this->post(route('scrapyard.requests.complete', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest));

        $this->assertNotSame('available', $holdRequest->part->fresh()->status);
    }

    public function test_accepted_request_can_be_cancelled(): void
    {
        $client = User::factory()->create([
            'name' => 'Cancelled Lifecycle Client',
            'email' => 'cancelled-lifecycle@example.com',
            'phone' => '0696888888',
        ]);
        $holdRequest = $this->createHoldRequest(client: $client, status: 'accepted');
        $holdRequest->part->update([
            'status' => 'reserved',
            'is_published' => true,
        ]);

        $this->post(route('scrapyard.requests.cancel', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest))
            ->assertSessionHas('success', 'La mise de côté a été annulée.');

        $this->assertSame('cancelled', $holdRequest->fresh()->status);
        $this->assertNotNull($holdRequest->fresh()->handled_at);
        $this->assertNull($holdRequest->fresh()->reserved_until);
        $this->assertSame('available', $holdRequest->part->fresh()->status);
        $this->assertTrue($holdRequest->part->fresh()->is_published);

        $this->get(route('scrapyard.requests.show', $holdRequest))
            ->assertOk()
            ->assertDontSee('Cancelled Lifecycle Client')
            ->assertDontSee('cancelled-lifecycle@example.com')
            ->assertDontSee('0696888888');
    }

    public function test_published_part_is_visible_client_side_after_cancellation(): void
    {
        $holdRequest = $this->createHoldRequest(status: 'accepted');
        $holdRequest->part->update([
            'name' => 'Alternateur visible après annulation',
            'status' => 'reserved',
            'is_published' => true,
        ]);

        $this->post(route('scrapyard.requests.cancel', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest));

        $this->get(route('client.parts.index'))
            ->assertOk()
            ->assertSee('Alternateur visible après annulation');
    }

    public function test_completed_request_cannot_be_modified_by_lifecycle_actions(): void
    {
        $holdRequest = $this->createHoldRequest(status: 'completed');
        $holdRequest->part->update([
            'status' => 'sold',
            'is_published' => false,
        ]);

        $this->post(route('scrapyard.requests.complete', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest))
            ->assertSessionHas('error', 'Cette demande ne peut plus être modifiée.');

        $this->post(route('scrapyard.requests.cancel', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest))
            ->assertSessionHas('error', 'Cette demande ne peut plus être modifiée.');

        $this->assertSame('completed', $holdRequest->fresh()->status);
        $this->assertSame('sold', $holdRequest->part->fresh()->status);
        $this->assertFalse($holdRequest->part->fresh()->is_published);
    }

    public function test_only_accepted_requests_can_use_lifecycle_actions(): void
    {
        $scrapyard = $this->createScrapyard();

        foreach (['pending', 'refused', 'cancelled', 'completed'] as $status) {
            $holdRequest = $this->createHoldRequest(scrapyard: $scrapyard, status: $status);

            $this->post(route('scrapyard.requests.complete', $holdRequest))
                ->assertRedirect(route('scrapyard.requests.show', $holdRequest))
                ->assertSessionHas('error', 'Cette demande ne peut plus être modifiée.');

            $this->post(route('scrapyard.requests.cancel', $holdRequest))
                ->assertRedirect(route('scrapyard.requests.show', $holdRequest))
                ->assertSessionHas('error', 'Cette demande ne peut plus être modifiée.');

            $this->assertSame($status, $holdRequest->fresh()->status);
        }
    }

    public function test_scrapyard_cannot_complete_or_cancel_request_from_another_scrapyard(): void
    {
        $this->createScrapyard();
        $otherScrapyard = $this->createScrapyard('other-scrapyard');
        $holdRequest = $this->createHoldRequest($otherScrapyard, status: 'accepted');

        $this->post(route('scrapyard.requests.complete', $holdRequest))
            ->assertNotFound();

        $this->post(route('scrapyard.requests.cancel', $holdRequest))
            ->assertNotFound();

        $this->assertSame('accepted', $holdRequest->fresh()->status);
    }

    public function test_accepted_request_before_reserved_until_does_not_expire(): void
    {
        Carbon::setTestNow('2026-08-31 10:00:00');
        $holdRequest = $this->createHoldRequest(status: 'accepted');
        $holdRequest->update([
            'reserved_until' => now()->addHour(),
        ]);
        $holdRequest->part->update([
            'status' => 'reserved',
            'is_published' => true,
        ]);

        $this->artisan('requests:expire-reservations')
            ->expectsOutput('0 demandes expirées.')
            ->assertSuccessful();

        $this->assertSame('accepted', $holdRequest->fresh()->status);
        $this->assertSame('reserved', $holdRequest->part->fresh()->status);
    }

    public function test_accepted_request_after_reserved_until_expires(): void
    {
        Carbon::setTestNow('2026-08-31 10:00:00');
        $holdRequest = $this->createHoldRequest(status: 'accepted');
        $holdRequest->update([
            'reserved_until' => now()->subMinute(),
        ]);
        $holdRequest->part->update([
            'status' => 'reserved',
            'is_published' => true,
        ]);

        $this->artisan('requests:expire-reservations')
            ->expectsOutput('1 demande expirée.')
            ->assertSuccessful();

        $this->assertSame('expired', $holdRequest->fresh()->status);
        $this->assertNotNull($holdRequest->fresh()->handled_at);
        $this->assertTrue($holdRequest->fresh()->reserved_until->equalTo(now()->subMinute()));
        $this->assertSame('available', $holdRequest->part->fresh()->status);
        $this->assertTrue($holdRequest->part->fresh()->is_published);
    }

    public function test_published_part_is_visible_client_side_after_expiration(): void
    {
        Carbon::setTestNow('2026-08-31 10:00:00');
        $holdRequest = $this->createHoldRequest(status: 'accepted');
        $holdRequest->update([
            'reserved_until' => now()->subMinute(),
        ]);
        $holdRequest->part->update([
            'name' => 'Démarreur visible après expiration',
            'status' => 'reserved',
            'is_published' => true,
        ]);

        $this->artisan('requests:expire-reservations')
            ->assertSuccessful();

        $this->get(route('client.parts.index'))
            ->assertOk()
            ->assertSee('Démarreur visible après expiration');
    }

    public function test_unpublished_part_stays_hidden_client_side_after_expiration(): void
    {
        Carbon::setTestNow('2026-08-31 10:00:00');
        $holdRequest = $this->createHoldRequest(status: 'accepted');
        $holdRequest->update([
            'reserved_until' => now()->subMinute(),
        ]);
        $holdRequest->part->update([
            'name' => 'Démarreur non publié après expiration',
            'status' => 'reserved',
            'is_published' => false,
        ]);

        $this->artisan('requests:expire-reservations')
            ->assertSuccessful();

        $this->get(route('client.parts.index'))
            ->assertOk()
            ->assertDontSee('Démarreur non publié après expiration');
    }

    public function test_expired_request_cannot_be_accepted_refused_completed_or_cancelled(): void
    {
        $holdRequest = $this->createHoldRequest(status: 'expired');
        $holdRequest->part->update([
            'status' => 'available',
            'is_published' => true,
        ]);

        $this->post(route('scrapyard.requests.accept', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest))
            ->assertSessionHas('error', 'Cette demande ne peut plus être traitée.');

        $this->post(route('scrapyard.requests.refuse', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest))
            ->assertSessionHas('error', 'Cette demande ne peut plus être traitée.');

        $this->post(route('scrapyard.requests.complete', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest))
            ->assertSessionHas('error', 'Cette demande ne peut plus être modifiée.');

        $this->post(route('scrapyard.requests.cancel', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest))
            ->assertSessionHas('error', 'Cette demande ne peut plus être modifiée.');

        $this->assertSame('expired', $holdRequest->fresh()->status);
        $this->assertSame('available', $holdRequest->part->fresh()->status);
        $this->assertTrue($holdRequest->part->fresh()->is_published);
    }

    public function test_expiration_command_is_idempotent(): void
    {
        Carbon::setTestNow('2026-08-31 10:00:00');
        $holdRequest = $this->createHoldRequest(status: 'accepted');
        $holdRequest->update([
            'reserved_until' => now()->subMinute(),
        ]);
        $holdRequest->part->update([
            'status' => 'reserved',
            'is_published' => true,
        ]);

        $this->artisan('requests:expire-reservations')
            ->expectsOutput('1 demande expirée.')
            ->assertSuccessful();

        $handledAt = $holdRequest->fresh()->handled_at;

        Carbon::setTestNow('2026-08-31 11:00:00');

        $this->artisan('requests:expire-reservations')
            ->expectsOutput('0 demandes expirées.')
            ->assertSuccessful();

        $this->assertSame('expired', $holdRequest->fresh()->status);
        $this->assertTrue($holdRequest->fresh()->handled_at->equalTo($handledAt));
        $this->assertSame('available', $holdRequest->part->fresh()->status);
    }

    private function createHoldRequest(
        ?Scrapyard $scrapyard = null,
        ?User $client = null,
        string $status = 'pending',
    ): PartHoldRequest {
        $scrapyard ??= $this->createScrapyard();
        $client ??= User::factory()->create([
            'name' => 'Client Test',
            'email' => 'client-test-' . uniqid() . '@example.com',
            'phone' => '0696000001',
        ]);

        $vehicle = Vehicle::query()->create([
            'scrapyard_id' => $scrapyard->id,
            'brand' => 'Renault',
            'model' => 'Clio',
            'year' => 2018,
            'engine' => '1.5 dCi',
        ]);

        $part = Part::query()->create([
            'vehicle_id' => $vehicle->id,
            'name' => 'Phare avant droit',
            'category' => 'Optique',
            'condition' => 'used_good',
            'status' => 'available',
            'price' => 85,
            'is_published' => true,
        ]);

        return PartHoldRequest::query()->create([
            'user_id' => $client->id,
            'part_id' => $part->id,
            'status' => $status,
            'customer_message' => 'Je souhaite réserver cette pièce.',
        ]);
    }

    private function createHoldRequestForPart(
        Part $part,
        ?User $client = null,
        string $status = 'pending',
    ): PartHoldRequest {
        $client ??= User::factory()->create([
            'name' => 'Client Test',
            'email' => 'client-test-' . uniqid() . '@example.com',
            'phone' => '0696000001',
        ]);

        return PartHoldRequest::query()->create([
            'user_id' => $client->id,
            'part_id' => $part->id,
            'status' => $status,
            'customer_message' => 'Je souhaite réserver cette pièce.',
        ]);
    }

    private function createScrapyard(?string $slug = null): Scrapyard
    {
        $user = User::factory()->create([
            'name' => 'Casse Test',
            'email' => 'scrapyard-' . uniqid() . '@example.com',
        ]);

        return Scrapyard::query()->create([
            'user_id' => $user->id,
            'name' => 'Casse Martinique',
            'slug' => $slug ?? 'casse-' . uniqid(),
            'city' => 'Fort-de-France',
            'is_active' => true,
        ]);
    }
}
