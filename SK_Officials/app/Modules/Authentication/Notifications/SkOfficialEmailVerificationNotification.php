<?php

namespace App\Modules\Authentication\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class SkOfficialEmailVerificationNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expireMinutes = (int) Config::get('auth.verification.expire', 60);
        $verificationUrl = URL::temporarySignedRoute(
            'sk_official.verification.verify',
            Carbon::now()->addMinutes($expireMinutes),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        return (new MailMessage)
            ->subject('SK Officials — Verify Your Email')
            ->greeting('Hello!')
            ->line('Please verify your SK Officials account to complete secure access setup.')
            ->action('Verify Email Address', $verificationUrl)
            ->line("This verification link expires in {$expireMinutes} minutes.")
            ->line('If you did not request this, no further action is required.')
            ->salutation('SK OnePortal — Santa Cruz, Laguna');
    }
}
