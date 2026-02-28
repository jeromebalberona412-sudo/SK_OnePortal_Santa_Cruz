<?php

namespace App\Modules\Authentication\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SessionTakeoverOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected string $otpCode) {}

    public function otpCode(): string
    {
        return $this->otpCode;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Security Verification Code')
            ->greeting('Security Verification Required')
            ->line('A login attempt was detected while your account is active.')
            ->line('Your verification code: '.$this->otpCode)
            ->line('This code expires in 5 minutes.');
    }
}
