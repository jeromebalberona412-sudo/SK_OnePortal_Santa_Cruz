<?php

namespace App\Modules\KKProfilingRequests\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KabataanApprovedNotification extends Notification
{
    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your KK Profiling Registration Has Been Approved')
            ->greeting('Hello, ' . $notifiable->name . '!')
            ->line('Great news! Your KK Profiling registration has been reviewed and approved by your barangay SK officials.')
            ->line('Your account is now active. You can log in to the SK OnePortal Youth Portal.')
            ->action('Login Now', url('/login'))
            ->line('Welcome to the Katipunan ng Kabataan!');
    }
}
