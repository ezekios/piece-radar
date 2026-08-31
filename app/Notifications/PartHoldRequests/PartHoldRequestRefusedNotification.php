<?php

namespace App\Notifications\PartHoldRequests;

use Illuminate\Notifications\Messages\MailMessage;

class PartHoldRequestRefusedNotification extends BasePartHoldRequestNotification
{
    public function toMail(object $notifiable): MailMessage
    {
        return $this->mailMessage('Votre demande a été refusée')
            ->line('Votre demande de mise de côté a été refusée.')
            ->line('Pièce : ' . $this->partName())
            ->line('Casse : ' . $this->scrapyardName())
            ->action('Voir ma demande', $this->clientRequestUrl());
    }
}
