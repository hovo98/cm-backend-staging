<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

/**
 * Class SuspiciousLocationsAdmin
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class SuspiciousLocationsAdmin extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $company;

    /**
     * SuspiciousLocationsAdmin constructor.
     *
     * @param  string  $name
     * @param  string  $company
     */
    public function __construct(string $name, string $company)
    {
        $this->queue = 'emails';
        $this->name = $name;
        $this->company = $company;
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
        $mailMessage = new MailMessage();

        $mailMessage->view = 'mail.suspiciousLocations';
        $mailMessage->viewData = [
            'name' => $this->name,
            'company' => $this->company,
            'year' => date('Y'),
        ];

        return $mailMessage
            ->subject(Lang::get('Suspicious locations alert'));
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
