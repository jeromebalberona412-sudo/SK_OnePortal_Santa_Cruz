<?php

namespace App\Modules\Program_Management\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProgramApplicationStatusNotification extends Notification
{
    public function __construct(
        public string $applicantName,
        public string $programName,
        public string $eventType,
        public ?string $reason = null,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $program = $this->programName !== '' ? $this->programName : 'your program';

        $message = (new MailMessage)
            ->greeting('Hello '.$this->applicantName.'!');

        return match ($this->eventType) {
            'approved' => $message
                ->subject('Your Application Has Been Approved')
                ->line("Good news! Your application for {$program} has been approved by the SK Officials.")
                ->line('You may log in to the Kabataan portal to view your application status and next steps.')
                ->line('Thank you for participating in SK Santa Cruz youth programs.'),

            'rejected' => $message
                ->subject('Update on Your Application')
                ->line("Your application for {$program} was reviewed and could not be approved at this time.")
                ->when($this->reason, fn (MailMessage $mail) => $mail->line('Reason: '.$this->reason))
                ->line('You may log in to the Kabataan portal for more details.'),

            'revoked' => $message
                ->subject('Update on Your Application Approval')
                ->line("The approval for your application to {$program} has been revoked by the SK Officials.")
                ->line('Your application has been returned to pending review in Scholarship Applications.')
                ->when($this->reason, fn (MailMessage $mail) => $mail->line('Reason: '.$this->reason))
                ->line('You may log in to the Kabataan portal for more details.'),

            'restored' => $message
                ->subject('Your Application Has Been Restored')
                ->line("Your application for {$program} has been restored and is back under review by the SK Officials.")
                ->line('You may log in to the Kabataan portal to view your application status.'),

            default => $message
                ->subject('Application Status Update')
                ->line("There is an update on your application for {$program}.")
                ->line('Please log in to the Kabataan portal for details.'),
        };
    }
}
