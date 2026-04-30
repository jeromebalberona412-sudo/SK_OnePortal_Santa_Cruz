<?php

namespace App\Modules\Accounts\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $token,
        public string $baseUrl,
        public string $audienceLabel,
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
        $baseUrl = rtrim($this->baseUrl, '/');
        $email = urlencode((string) $notifiable->getEmailForPasswordReset());
        $resetUrl = "{$baseUrl}/reset-password/{$this->token}?email={$email}";
        $expiry = (int) config('auth.passwords.'.config('auth.defaults.passwords', 'users').'.expire');

        return (new MailMessage)
            ->subject('Set up your '.$this->audienceLabel.' account password')
            ->greeting('Hello!')
            ->line('You have been invited to set up a password for your '.$this->audienceLabel.' account.')
            ->action('Set Up Password', $resetUrl)
            ->line('This link will expire in '.$expiry.' minutes.')
            ->line('If you did not expect this email, you can ignore it.');
    }
}
