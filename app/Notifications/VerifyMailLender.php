<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as IlluminateVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;

/**
 * Class VerifyMailLender
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class VerifyMailLender extends IlluminateVerifyEmail implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public $sentVerifyEmailAt;

    public function __construct($sentVerifyEmailAt)
    {
        $this->queue = 'emails';

        // Delay email only when is sent for the first time
        $this->sentVerifyEmailAt = $sentVerifyEmailAt;
        if (! $this->sentVerifyEmailAt) {
            $this->delay(now()->addMinutes(1));
        }
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

        $mailMessage->view = 'mail.verificationMailLender';
        $mailMessage->viewData = [
            'user' => $notifiable,
            'url' => $verificationUrl,
            'count' => 60,
            'year' => date('Y'),
        ];

        return $mailMessage
            ->mailer(config('mail.alternative_mailer'))
            ->subject(Lang::get('Almost Done! Please Verify Your Account'))
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
