<?php

namespace App\Modules\Authentication\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class SkFedVerifyEmailNotification extends VerifyEmail
{
    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'skfed.verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 10)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verify Your SK Federation Account')
            ->greeting('Hello!')
            ->line('Please verify your SK Federation account to complete secure access setup.')
            ->action('Verify Email Address', $verificationUrl)
            ->line('This verification link expires shortly for your security.')
            ->line('If you did not request this, no further action is required.');
    }
}
