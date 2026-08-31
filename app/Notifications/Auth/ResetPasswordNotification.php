<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Réinitialiser votre mot de passe')
            ->greeting('Bonjour,')
            ->line('Vous recevez cet email car une demande de réinitialisation de mot de passe a été faite pour votre compte Pièce Radar.')
            ->action('Réinitialiser mon mot de passe', $url)
            ->line('Ce lien de réinitialisation expirera dans ' . config('auth.passwords.' . config('auth.defaults.passwords') . '.expire') . ' minutes.')
            ->line('Si vous n’êtes pas à l’origine de cette demande, aucune action n’est nécessaire.')
            ->salutation("Cordialement,\nL'équipe Pièce Radar");
    }
}
