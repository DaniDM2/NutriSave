<?php

declare(strict_types=1);

namespace App\Infrastructure\Mail;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class HouseholdInvitationNotification extends Notification
{
    public function __construct(
        public string $householdName,
        public string $plainToken,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Invitación a {$this->householdName} en NutriSave")
            ->line("Has sido invitado a unirte al hogar {$this->householdName}.")
            ->action('Aceptar invitación', route('household.invitations.accept', $this->plainToken))
            ->line('Este enlace caduca en 7 días.');
    }
}
