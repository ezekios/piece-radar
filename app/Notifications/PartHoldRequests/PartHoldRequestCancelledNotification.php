<?php

namespace App\Notifications\PartHoldRequests;

use Illuminate\Notifications\Messages\MailMessage;

class PartHoldRequestCancelledNotification extends BasePartHoldRequestNotification
{
    public function toMail(object $notifiable): MailMessage
    {
        return $this->mailMessage('Votre réservation a été annulée')
            ->line('La mise de côté liée à votre demande a été annulée.')
            ->line('Pièce : ' . $this->partName())
            ->line('Cette pièce n’est plus réservée pour vous.')
            ->action('Voir ma demande', $this->clientRequestUrl());
    }
}
