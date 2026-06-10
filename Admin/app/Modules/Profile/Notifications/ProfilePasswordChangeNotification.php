<?php

namespace App\Modules\Profile\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProfilePasswordChangeNotification extends Notification
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
            ->subject('Set Your New OnePortal Admin Password')
            ->greeting('Hello!')
            ->line('You requested to change the password on your OnePortal Admin account.')
            ->line('Email: '.$notifiable->email)
            ->action('Set New Password', $resetUrl)
            ->line('Click the button above to open the password form. Enter your new password and confirm it to finish.')
            ->line('You will be signed out on all devices and redirected to the login page after changing your password.')
            ->line("This link expires in {$expiry} minutes. If you did not request this, you can ignore this email.");
    }
}
