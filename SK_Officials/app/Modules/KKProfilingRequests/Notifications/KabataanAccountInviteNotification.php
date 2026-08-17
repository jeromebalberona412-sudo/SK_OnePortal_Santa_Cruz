<?php

namespace App\Modules\KKProfilingRequests\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KabataanAccountInviteNotification extends Notification
{
    public function __construct(
        public string $fullName,
        public string $activationUrl,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $name = trim($this->fullName) !== '' ? $this->fullName : 'Kabataan';

        return (new MailMessage)
            ->subject('Your Kabataan OnePortal Account Activation')
            ->greeting('Hello '.$name.',')
            ->line('Your email address has been added to your KK Profiling record by your SK Officials.')
            ->line('You can now activate your Kabataan OnePortal account by using the secure button below:')
            ->action('Set Up My Account', $this->activationUrl)
            ->line('This link is temporary and can only be used once. It expires in 24 hours. Do not share it with anyone.')
            ->line('After activating your account, you can access the Kabataan system and available SK programs and services for your barangay.')
            ->line('If you did not request this update, please contact your SK Officials.');
    }
}
