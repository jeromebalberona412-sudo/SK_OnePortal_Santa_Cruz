<?php

namespace App\Modules\Authentication\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminPasswordResetNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $token,
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
        $baseUrl = rtrim((string) config('app.url'), '/');
        $email = urlencode((string) $notifiable->getEmailForPasswordReset());
        $resetUrl = "{$baseUrl}/reset-password/{$this->token}?email={$email}";
        $expiry = (int) config('auth.passwords.'.config('auth.defaults.passwords', 'users').'.expire', 60);

        return (new MailMessage)
            ->subject('SK One Portal Administrator Password Setup')
            ->greeting('Welcome to SK One Portal.')
            ->line('For security purposes, you must create your own administrator password before accessing the dashboard.')
            ->action('Secure Password Setup Link', $resetUrl)
            ->line("This link expires in {$expiry} minutes.");
    }
}
