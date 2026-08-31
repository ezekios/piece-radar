<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Vérifiez votre adresse email')
            ->greeting('Bonjour,')
            ->line('Bienvenue sur Pièce Radar.')
            ->line('Veuillez confirmer votre adresse email pour accéder à votre espace client.')
            ->action('Vérifier mon adresse email', $url)
            ->line('Si vous n’avez pas créé de compte, aucune action n’est nécessaire.')
            ->salutation("Cordialement,\nL'équipe Pièce Radar");
    }
}
