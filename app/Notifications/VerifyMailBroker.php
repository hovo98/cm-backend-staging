<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as IlluminateVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;

/**
 * Class VerifyMailBroker
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class VerifyMailBroker extends IlluminateVerifyEmail implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
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
        $verificationUrl = $this->verificationUrl($notifiable);

        $mailMessage = new MailMessage();

        $mailMessage->view = 'mail.verificationMailBroker';
        $mailMessage->viewData = [
            'user' => $notifiable,
            'url' => $verificationUrl,
            'count' => 60,
            'year' => date('Y'),
        ];

        return $mailMessage
            ->mailer(config('mail.alternative_mailer'))
            ->subject(Lang::get('Almost Done! Verify Your Account'))
            ->bcc(explode(',', config('mail.bcc')));
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    public function verificationUrl($notifiable)
    {
        return config('app.frontend_url').'/verify?id='.encrypt([
            'email' => $notifiable->getEmailForVerification(),
            'expiration' => Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
        ], true);
    }
}
