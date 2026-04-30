<?php

namespace App\Modules\Authentication\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TakeoverOtpNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $otp,
    ) {}

    /**
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $expirationMinutes = (int) config('sk_official_auth.single_session.otp_expiration_minutes', 5);

        return (new MailMessage)
            ->subject('SK Officials — Session Takeover OTP')
            ->greeting('Hello!')
            ->line('A login attempt was made to your SK Officials account from a new location while your account was already active.')
            ->line('Your one-time password (OTP) to authorize this session takeover is:')
            ->line('**'.$this->otp.'**')
            ->line('This OTP expires in '.$expirationMinutes.' minutes.')
            ->line('If you did not initiate this login, please change your password immediately.');
    }
}
