<?php

namespace App\Modules\Authentication\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminLoginVerificationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $plainToken,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('verification.verify', [
            'id' => $notifiable->id,
            'token' => $this->plainToken,
        ], false));

        return (new MailMessage)
            ->subject('Verify Your OnePortal Admin Login')
            ->greeting('Hello!')
            ->line('Please verify your email to continue to the OnePortal Admin dashboard.')
            ->line('Email: '.$notifiable->email)
            ->action('Verify Email & Continue', $url)
            ->line('This link expires in 60 minutes. If you did not attempt to sign in, you can ignore this email.');
    }
}
