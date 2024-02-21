<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

/**
 * Class FinishSecondStep
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class FinishSecondStep extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

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
        $url = $this->finishSecondStepUrl($notifiable);
        $mailMessage = new MailMessage();

        $mailMessage->view = 'mail.finishSecondStep';

        $mailMessage->viewData = [
            'user' => $notifiable->first_name,
            'url' => $url,
            'year' => date('Y'),
        ];

        return $mailMessage
            ->subject(Lang::get('Your account is inactive'));
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

    /**
     * @param $notifiable
     * @return string
     */
    public static function finishSecondStepUrl($notifiable): string
    {
        $tempToken = encrypt([
            'id' => $notifiable->id,
            'email' => $notifiable->email,
        ]);

        return config('app.frontend_url').'/sign-up/lender?id='.$tempToken;
    }
}
