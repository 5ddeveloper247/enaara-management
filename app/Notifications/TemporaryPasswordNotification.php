<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TemporaryPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $plainPassword,
        private readonly string $reason = 'welcome'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $loginUrl = url(route('login', [], false));
        $subject    = $this->reason === 'admin_reset'
            ? 'Your password was reset'
            : 'Your account has been created';

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . ($notifiable->name ?? '') . ',')
            ->line($this->reason === 'admin_reset'
                ? 'An administrator reset your password. Use the temporary password below to sign in, then you will be asked to choose a new password.'
                : 'Your user account has been created. Use the temporary password below to sign in; you will be asked to change it after your first login.')
            ->line('Email: ' . $notifiable->email)
            ->line('Temporary password: ' . $this->plainPassword)
            ->action('Sign in', $loginUrl)
            ->line('If you did not expect this email, contact your administrator.');
    }
}
