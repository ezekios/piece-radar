<?php

namespace App\Notifications\PartHoldRequests;

use Illuminate\Notifications\Messages\MailMessage;

class NewPartHoldRequestNotification extends BasePartHoldRequestNotification
{
    public function toMail(object $notifiable): MailMessage
    {
        $message = $this->mailMessage('Nouvelle demande de mise de côté')
            ->line('Une nouvelle demande de mise de côté a été reçue sur Pièce Radar.')
            ->line('Pièce : ' . $this->partName())
            ->line('Véhicule : ' . $this->vehicleLabel())
            ->line('Date de demande : ' . $this->displayDate($this->request()->created_at));

        if ($referenceLine = $this->referenceLine()) {
            $message->line($referenceLine);
        }

        return $message
            ->action('Voir la demande', $this->scrapyardRequestUrl())
            ->line('Les coordonnées du client seront disponibles uniquement après acceptation.');
    }
}
