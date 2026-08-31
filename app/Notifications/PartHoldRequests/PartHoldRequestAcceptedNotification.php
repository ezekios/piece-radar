<?php

namespace App\Notifications\PartHoldRequests;

use Illuminate\Notifications\Messages\MailMessage;

class PartHoldRequestAcceptedNotification extends BasePartHoldRequestNotification
{
    public function toMail(object $notifiable): MailMessage
    {
        return $this->mailMessage('Votre demande a été acceptée')
            ->line('Votre demande de mise de côté a été acceptée.')
            ->line('Pièce : ' . $this->partName())
            ->line('Casse : ' . $this->scrapyardName())
            ->line('Votre pièce est réservée pendant 48 heures.')
            ->line('Expiration de la réservation : ' . $this->displayDate($this->request()->reserved_until))
            ->action('Voir ma demande', $this->clientRequestUrl());
    }
}
