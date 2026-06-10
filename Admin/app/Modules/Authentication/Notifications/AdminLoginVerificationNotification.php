<?php

namespace App\Modules\Authentication\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
class AdminLoginVerificationNotification extends Notification
{
    use Queueable;

    private const MAIL_SENDER_NAME = 'SK OnePortal';

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
        $fromAddress = (string) config('mail.from.address', 'skoneportal@gmail.com');
        $fromName = self::MAIL_SENDER_NAME;

        $url = url(route('verification.verify', [
            'id' => $notifiable->id,
            'token' => $this->plainToken,
        ], false));

        return (new MailMessage)
            ->from($fromAddress, $fromName)
            ->replyTo($fromAddress, $fromName)
            ->subject('Verify Your SK OnePortal Admin Login')
            ->greeting('Hello!')
            ->salutation('— SK OnePortal')
            ->line('Please verify your email to continue to the SK OnePortal Admin dashboard.')
            ->line('Email: '.$notifiable->email)
            ->action('Verify Email & Continue', $url)
            ->line('This link expires in 60 minutes. If you did not attempt to sign in, you can ignore this email.');
    }
}
