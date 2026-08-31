<?php

namespace App\Notifications\PartHoldRequests;

use Illuminate\Notifications\Messages\MailMessage;

class PartHoldRequestCompletedNotification extends BasePartHoldRequestNotification
{
    public function toMail(object $notifiable): MailMessage
    {
        return $this->mailMessage('Votre demande est terminée')
            ->line('Votre demande de mise de côté est maintenant terminée.')
            ->line('Pièce : ' . $this->partName())
            ->line('Casse : ' . $this->scrapyardName())
            ->action('Voir ma demande', $this->clientRequestUrl());
    }
}
