<?php

namespace App\Modules\KKProfilingRequests\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KabataanRejectedNotification extends Notification
{
    public function __construct(public string $reasons) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your KK Profiling Registration Was Not Approved')
            ->greeting('Hello, ' . $notifiable->name . '!')
            ->line('We regret to inform you that your KK Profiling registration has been reviewed and could not be approved at this time.')
            ->line('**Reason(s):** ' . $this->reasons)
            ->line('Please visit your barangay SK office for assistance or to resubmit your registration with the correct information.')
            ->line('If you believe this is an error, please contact your barangay SK officials.');
    }
}
