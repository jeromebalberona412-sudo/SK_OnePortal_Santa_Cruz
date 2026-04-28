<?php

namespace App\Modules\Authentication\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SessionTakeoverOtpNotification extends Notification
{
    use Queueable;

    public function __construct(protected string $otpCode) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function otpCode(): string
    {
        return $this->otpCode;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('SK Official Session Verification Code')
            ->greeting('Hello!')
            ->line('A sign-in attempt was detected while your SK Official account already has an active session.')
            ->line('Use this verification code to continue on the new device:')
            ->line($this->otpCode)
            ->line('If this was not you, ignore this message and keep your current session active.');
    }
}
