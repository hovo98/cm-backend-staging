<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

/**
 * Class ApproveCompanyLender
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class ApproveCompanyLender extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var int
     */
    protected $company_id;

    /**
     * @var string
     */
    protected $domain;

    /**
     * ApproveCompany constructor.
     *
     * @param  int  $company_id
     * @param  string  $domain
     */
    public function __construct(int $company_id, string $domain)
    {
        $this->queue = 'emails';
        $this->company_id = $company_id;
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
        $approveUrl = $this->approveUrl($this->company_id);

        $mailMessage = new MailMessage();

        $mailMessage->view = 'mail.approveCompanyLender';
        $mailMessage->viewData = [
            'user' => $notifiable,
            'url' => $approveUrl,
            'domain' => $this->domain,
            'year' => date('Y'),
        ];

        return $mailMessage
            ->mailer(config('mail.alternative_mailer'))
            ->subject(Lang::get('Approve Company domain '.$this->domain));
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param $company_id
     * @return string
     */
    public function approveUrl($company_id)
    {
        return config('app.url').'/company/'.$company_id;
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
