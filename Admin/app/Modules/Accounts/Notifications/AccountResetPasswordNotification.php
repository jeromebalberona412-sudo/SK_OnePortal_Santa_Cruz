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
            ->subject('Reset Your '.$this->audienceLabel.' Password')
            ->greeting('Hello!')
            ->line('We received a request to reset the password for your '.$this->audienceLabel.' account.')
            ->action('Create New Password', $resetUrl)
            ->line('This password reset link will expire in '.$expiry.' minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}
