<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

/**
 * Class ApprovedDomainLenders
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class ApprovedDomainLenders extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var string
     */
    protected $domain;

    /**
     * ApprovedDomainBrokers constructor.
     *
     * @param  string  $domain
     */
    public function __construct(string $domain)
    {
        $this->queue = 'emails';
        $this->domain = $domain;
    }

    /**
     * The callback that should be used to build the mail message.
     *
     * @var \Closure|null
     */
    public static $toMailCallback;

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
        $lenderUrl = $this->lenderUrl();

        $mailMessage = new MailMessage();

        $mailMessage->view = 'mail.approvedDomainLenders';
        $mailMessage->viewData = [
            'user' => $notifiable,
            'url' => $lenderUrl,
            'year' => date('Y'),
        ];

        return $mailMessage
            ->mailer(config('mail.alternative_mailer'))
            ->subject(Lang::get('Congratulations '.$notifiable->first_name.'! Your account was approved'));
    }

    /**
     * Get the broker deals URL for the given notifiable.
     *
     * @return string
     */
    public function lenderUrl()
    {
        return config('app.frontend_url').'/lender-deals/';
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
