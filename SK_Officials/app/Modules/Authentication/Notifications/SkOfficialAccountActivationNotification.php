<?php

namespace App\Modules\Authentication\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SkOfficialAccountActivationNotification extends Notification
{
    public function __construct(public string $token) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = trim((string) ($notifiable->name ?? '')) !== ''
            ? trim((string) $notifiable->name)
            : 'SK Official';
        $email = (string) $notifiable->getEmailForPasswordReset();
        $url = url(route('account.activation.show', [
            'token' => $this->token,
            'email' => $email,
        ], false));

        return (new MailMessage)
            ->subject('Activate Your SK OnePortal Account')
            ->greeting('Hello '.$name.',')
            ->line('Your account has been created for the SK OnePortal system.')
            ->line('Please activate your account and set your password using the button below:')
            ->action('Activate / Set Up My Account', $url)
            ->line('This activation link is temporary and can only be used once. It expires in '.$this->expiryLabel().'.')
            ->line('Important: If the link expires, you do not need to ask the SK Federation to resend the activation email.')
            ->line('Simply go to the SK Officials login page and select: Activate Account')
            ->line('Enter the email address registered to your account and request a new activation link.')
            ->line('If you did not expect this email, you can ignore it.');
    }

    private function expiryLabel(): string
    {
        $minutes = max(1, (int) config('sk_official_auth.account_activation.expire_minutes', 1440));

        if ($minutes >= 1440) {
            $days = (int) round($minutes / 1440);

            return $days.' day'.($days === 1 ? '' : 's');
        }

        return $minutes.' minute'.($minutes === 1 ? '' : 's');
    }
}
