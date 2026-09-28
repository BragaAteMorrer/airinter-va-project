<?php

namespace App\Notifications;

use App\Models\SecurityEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SecurityAlertNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly SecurityEvent $event)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $labels = [
            'password.changed' => 'Votre mot de passe Argos a été modifié.',
            'mfa.enabled' => 'La double authentification a été activée sur votre compte Argos.',
            'mfa.disabled' => 'La double authentification a été désactivée sur votre compte Argos.',
            'passkey.registered' => 'Une nouvelle passkey a été ajoutée à votre compte Argos.',
            'passkey.deleted' => 'Une passkey a été supprimée de votre compte Argos.',
            'oauth.refresh.reuse_detected' => 'Argos a détecté la réutilisation d’un ancien jeton de connexion.',
            'login.new_context' => 'Une connexion Argos a été détectée depuis un nouveau contexte.',
        ];

        $message = $labels[$this->event->type] ?? 'Un événement de sécurité Argos a été enregistré.';

        return (new MailMessage)
            ->subject('Alerte de sécurité Argos')
            ->greeting('Sécurité Argos')
            ->line($message)
            ->when($this->event->ip_address, fn (MailMessage $mail) => $mail->line('Adresse IP : '.$this->event->ip_address))
            ->line('Si cette action vient de vous, aucune intervention n’est nécessaire.')
            ->action('Ouvrir mon compte Argos', url('/account'))
            ->line('Si vous ne reconnaissez pas cette activité, changez votre mot de passe et révoquez les sessions/applications actives.');
    }
}
