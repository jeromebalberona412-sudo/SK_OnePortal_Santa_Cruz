<?php

namespace App\Modules\Authentication\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeviceVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $verificationUrl,
        protected ?string $ipAddress,
        protected ?string $userAgent,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verify New Device Sign-In')
            ->greeting('Security Check Required')
            ->line('A sign-in attempt from an unrecognized device was detected for your SK Federation account.')
            ->line('IP Address: '.($this->ipAddress ?? 'unknown'))
            ->line('Device: '.substr((string) ($this->userAgent ?? 'unknown'), 0, 180))
            ->action('Verify This Device', $this->verificationUrl)
            ->line('This link is single-use and expires soon for your security.');
    }
}
