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
        $isReset    = $this->reason === 'admin_reset';
        
        $subject    = $isReset ? 'Your password was reset' : 'Your account has been created';
        $title      = $isReset ? 'Reset Your Password' : 'Your Account is Ready';
        $bodyText   = $isReset 
            ? 'An administrator has reset your password. Please use the temporary credentials below to log in and set a new password.'
            : 'Your user account has been successfully created. You can now log in using the temporary credentials provided below.';
        $actionText = $isReset ? 'Reset Password' : 'Sign In Now';

        return (new MailMessage)
            ->subject($subject)
            ->view('admin.emails.auth_notification', [
                'subject'    => $subject,
                'title'      => $title,
                'name'       => $notifiable->name ?? 'User',
                'email'      => $notifiable->email,
                'password'   => $this->plainPassword,
                'bodyText'   => $bodyText,
                'actionUrl'  => $loginUrl,
                'actionText' => $actionText,
            ]);
    }
}
