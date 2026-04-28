<?php

namespace App\Modules\Authentication\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLocationLoginNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<int, string>  $signals
     */
    public function __construct(
        protected string $ipAddress,
        protected ?string $userAgent,
        protected array $signals = [],
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
        return (new MailMessage)
            ->subject('New SK Official Login Alert')
            ->greeting('Hello!')
            ->line('A sign-in to your SK Official account was detected.')
            ->line('IP address: '.$this->ipAddress)
            ->line('Device: '.($this->userAgent ?: 'Unknown'))
            ->line('If this was not you, reset your password immediately.');
    }
}
