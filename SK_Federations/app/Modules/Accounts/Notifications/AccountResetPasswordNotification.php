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
        $expiryLabel = $this->passwordExpiryLabel();
        $sentAt = now()->timezone(config('app.timezone', 'UTC'))->format('M j, Y g:i A');

        return (new MailMessage)
            ->subject('Set up your '.$this->audienceLabel.' account password ('.$sentAt.')')
            ->greeting('Hello!')
            ->line('You have been invited to set up a password for your '.$this->audienceLabel.' account.')
            ->line('Invitation sent on '.$sentAt.'.')
            ->action('Set Your Password', $resetUrl)
            ->line('This link will expire in '.$expiryLabel.'.')
            ->line('If you did not expect this email, you can ignore it.');
    }

    private function passwordExpiryLabel(): string
    {
        $minutes = (int) config('accounts.invitation_expire_minutes', 60 * 24);

        if ($minutes >= 1440) {
            $days = (int) round($minutes / 1440);

            return $days.' day'.($days === 1 ? '' : 's');
        }

        return $minutes.' minute'.($minutes === 1 ? '' : 's');
    }
}
