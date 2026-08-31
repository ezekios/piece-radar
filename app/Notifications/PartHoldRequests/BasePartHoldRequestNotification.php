<?php

namespace App\Notifications\PartHoldRequests;

use App\Models\PartHoldRequest;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

abstract class BasePartHoldRequestNotification extends Notification
{
    public function __construct(protected PartHoldRequest $partHoldRequest)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    protected function request(): PartHoldRequest
    {
        return $this->partHoldRequest->loadMissing(['part.vehicle.scrapyard', 'user']);
    }

    protected function partName(): string
    {
        return $this->request()->part?->name ?? 'Pièce non renseignée';
    }

    protected function vehicleLabel(): string
    {
        $vehicle = $this->request()->part?->vehicle;

        if (! $vehicle) {
            return 'Véhicule non renseigné';
        }

        return trim($vehicle->brand . ' ' . $vehicle->model . ($vehicle->year ? ' ' . $vehicle->year : ''));
    }

    protected function scrapyardName(): string
    {
        return $this->request()->part?->vehicle?->scrapyard?->name ?? 'Casse non renseignée';
    }

    protected function referenceLine(): ?string
    {
        $reference = $this->request()->part?->reference;

        return $reference ? 'Référence : ' . $reference : null;
    }

    protected function scrapyardRequestUrl(): string
    {
        return route('scrapyard.requests.show', $this->request());
    }

    protected function clientRequestUrl(): string
    {
        return route('client.requests.show', $this->request());
    }

    protected function displayDate(?\Carbon\CarbonInterface $date): string
    {
        if (! $date) {
            return 'Non renseignée';
        }

        return $date
            ->copy()
            ->timezone(config('app.display_timezone', 'UTC'))
            ->locale(app()->getLocale())
            ->translatedFormat('d/m/Y à H:i');
    }

    protected function mailMessage(string $subject): MailMessage
    {
        return (new MailMessage)
            ->subject($subject)
            ->greeting('Bonjour,');
    }
}
