<?php

namespace RefinedDigital\CMS\Modules\Users\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class WelcomeNotification extends ResetPassword
{
    /**
     * Signed link so the reset page knows to accept the 7 day invite token.
     */
    protected function resetUrl($notifiable)
    {
        return URL::temporarySignedRoute('password.reset', now()->addDays(7), [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }

    /**
     * Build the welcome email.
     */
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject('Welcome to '.config('app.name'))
            ->line('An account has been created for you at '.config('app.name').'.')
            ->action('Set your password', $url)
            ->line('This link will expire in 7 days. After that, use "Forgot password?" on the login page.');
    }
}
