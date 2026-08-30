<?php

namespace Tests\Feature;

use App\Models\Part;
use App\Models\PartHoldRequest;
use App\Models\Scrapyard;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScrapyardRequestAcceptanceTest extends TestCase
{
    use RefreshDatabase;

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
        $holdRequest = $this->createHoldRequest();

        $this->post(route('scrapyard.requests.accept', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest))
            ->assertSessionHas('success', 'La demande a été acceptée. La pièce est maintenant mise de côté.');

        $this->assertSame('accepted', $holdRequest->fresh()->status);
        $this->assertSame('reserved', $holdRequest->part->fresh()->status);
    }

    public function test_non_pending_requests_cannot_be_accepted_again(): void
    {
        $scrapyard = $this->createScrapyard();

        foreach (['accepted', 'refused', 'cancelled', 'completed'] as $status) {
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

    public function test_refusing_pending_request_still_works(): void
    {
        $holdRequest = $this->createHoldRequest();

        $this->post(route('scrapyard.requests.refuse', $holdRequest))
            ->assertRedirect(route('scrapyard.requests.show', $holdRequest))
            ->assertSessionHas('success', 'La demande a été refusée.');

        $this->assertSame('refused', $holdRequest->fresh()->status);
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
