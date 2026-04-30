<?php

namespace App\Modules\Authentication\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class SkOfficialResetPasswordNotification extends ResetPassword
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Reset Your SK Officials Password')
            ->greeting('Hello!')
            ->line('You are receiving this email because we received a password reset request for your SK Officials account.')
            ->action('Reset Password', $url)
            ->line('This password reset link will expire in '.config('auth.passwords.'.config('fortify.passwords', config('auth.defaults.passwords', 'users')).'.expire', 60).' minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}
