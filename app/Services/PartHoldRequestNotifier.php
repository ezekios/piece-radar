<?php

namespace App\Services;

use App\Models\PartHoldRequest;
use App\Models\Scrapyard;
use App\Notifications\PartHoldRequests\NewPartHoldRequestNotification;
use App\Notifications\PartHoldRequests\PartHoldRequestAcceptedNotification;
use App\Notifications\PartHoldRequests\PartHoldRequestCancelledNotification;
use App\Notifications\PartHoldRequests\PartHoldRequestCompletedNotification;
use App\Notifications\PartHoldRequests\PartHoldRequestExpiredNotification;
use App\Notifications\PartHoldRequests\PartHoldRequestRefusedNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

class PartHoldRequestNotifier
{
    public function newRequest(PartHoldRequest $partHoldRequest): void
    {
        $partHoldRequest->loadMissing(['part.vehicle.scrapyard.user']);
        $scrapyard = $partHoldRequest->part?->vehicle?->scrapyard;

        if (! $scrapyard) {
            return;
        }

        $this->send($scrapyard, new NewPartHoldRequestNotification($partHoldRequest));
    }

    public function accepted(PartHoldRequest $partHoldRequest): void
    {
        $this->notifyClient($partHoldRequest, new PartHoldRequestAcceptedNotification($partHoldRequest));
    }

    public function refused(PartHoldRequest $partHoldRequest): void
    {
        $this->notifyClient($partHoldRequest, new PartHoldRequestRefusedNotification($partHoldRequest));
    }

    public function cancelled(PartHoldRequest $partHoldRequest): void
    {
        $this->notifyClient($partHoldRequest, new PartHoldRequestCancelledNotification($partHoldRequest));
    }

    public function completed(PartHoldRequest $partHoldRequest): void
    {
        $this->notifyClient($partHoldRequest, new PartHoldRequestCompletedNotification($partHoldRequest));
    }

    public function expired(PartHoldRequest $partHoldRequest): void
    {
        $this->notifyClient($partHoldRequest, new PartHoldRequestExpiredNotification($partHoldRequest));
    }

    private function notifyClient(PartHoldRequest $partHoldRequest, Notification $notification): void
    {
        $partHoldRequest->loadMissing('user');
        $client = $partHoldRequest->user;

        if (! $client) {
            return;
        }

        $this->send($client, $notification);
    }

    private function send(object $notifiable, Notification $notification): void
    {
        try {
            $notifiable->notify($notification);
        } catch (Throwable $exception) {
            Log::warning('Part hold request notification could not be sent.', [
                'notification' => $notification::class,
                'notifiable' => $notifiable::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
