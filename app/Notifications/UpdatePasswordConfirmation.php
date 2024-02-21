<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

/**
 * Class UpdatePasswordConfirmation
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class UpdatePasswordConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The callback that should be used to build the mail message.
     *
     * @var \Closure|null
     */
    public static $toMailCallback;

    public function __construct()
    {
        $this->queue = 'emails';
    }

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $loginUrl = config('app.frontend_url').'/login';

        $mailMessage = new MailMessage();

        $mailMessage->view = 'mail.updatePasswordConfirmation';
        $mailMessage->viewData = [
            'firstName' => $notifiable->first_name,
            'url' => $loginUrl,
            'year' => date('Y'),
        ];

        return $mailMessage
            ->mailer(config('mail.alternative_mailer'))
            ->subject(Lang::get('Password Change'));
    }

    /**
     * Set a callback that should be used when building the notification mail message.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function toMailUsing($callback)
    {
        static::$toMailCallback = $callback;
    }
}
