<?php

namespace App\Notifications\Application;

use App\Models\PartHoldRequest;
use Illuminate\Notifications\Notification;

class PartHoldRequestExpiredNotification extends Notification
{
    public function __construct(private PartHoldRequest $partHoldRequest)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'part_hold_request_expired',
            'title' => 'Réservation expirée',
            'message' => 'Le délai de réservation de 48 heures est terminé.',
            'url' => route('client.requests.show', $this->partHoldRequest),
            'part_hold_request_id' => $this->partHoldRequest->id,
        ];
    }
}
