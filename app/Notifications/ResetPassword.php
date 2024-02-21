<?php

namespace App\Notifications;

use App\User;
use Illuminate\Auth\Notifications\ResetPassword as IlluminateResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

/**
 * Class ResetPassword
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
class ResetPassword extends IlluminateResetPassword implements ShouldQueue
{
    use Queueable;

    public function __construct($token)
    {
        parent::__construct($token);
        $this->queue = 'emails';
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        if (static::$createUrlCallback && $notifiable instanceof User && $notifiable->role !== 'admin') {
            $url = call_user_func(static::$createUrlCallback, $notifiable, $this->token);
        } else {
            $url = url(route('password.reset', [
                'token' => $this->token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
        }

        $mailMessage = new MailMessage();

        $mailMessage->view = 'mail.resetPassword';
        $mailMessage->viewData = [
            'firstName' => $notifiable->first_name,
            'url' => $url,
            'count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
            'year' => date('Y'),
        ];

        return $mailMessage
            ->mailer(config('mail.alternative_mailer'))
            ->subject(Lang::get('Finance Lobby Password Reset'));
    }

    /**
     * Override the password reset url generation
     *
     * @param  mixed  $notifiable
     * @param  string  $token
     * @return string
     */
    public static function generateResetUrl($notifiable, $token): string
    {
        $token = encrypt([
            'email' => $notifiable->email,
            'token' => $token,
        ], true);

        return config('app.frontend_url').'/reset-password?id='.$token;
    }
}
