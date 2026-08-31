<?php

namespace App\Notifications\PartHoldRequests;

use Illuminate\Notifications\Messages\MailMessage;

class PartHoldRequestExpiredNotification extends BasePartHoldRequestNotification
{
    public function toMail(object $notifiable): MailMessage
    {
        return $this->mailMessage('Votre réservation a expiré')
            ->line('Votre délai de réservation est terminé.')
            ->line('Pièce : ' . $this->partName())
            ->line('La pièce peut de nouveau être disponible sur Pièce Radar.')
            ->action('Voir ma demande', $this->clientRequestUrl());
    }
}
