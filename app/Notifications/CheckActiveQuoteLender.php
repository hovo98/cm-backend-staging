<?php

namespace App\Notifications;

use App\Quote;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

/**
 * Class CheckActiveQuoteLender
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class CheckActiveQuoteLender extends Notification
{
    /**
     * @var string
     */
    protected $brokerName;

    /**
     * @var int
     */
    protected $quoteId;

    /**
     * @var int
     */
    protected $dealId;

    /**
     * @param $brokerName
     * @param $quoteId
     * @param $dealId
     */
    public function __construct($brokerName, $quoteId, $dealId)
    {
        $this->brokerName = $brokerName;
        $this->quoteId = $quoteId;
        $this->dealId = $dealId;
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
        return (new MailMessage())
            ->view('mail.checkActiveQuoteLender', [
                'lenderName' => $notifiable->first_name,
                'brokerName' => $this->brokerName,
                'urlYes' => $this->quoteActiveUrl($notifiable),
                'urlNo' => $this->quoteNotActiveUrl($notifiable),
                'dateOfQuoteFinished' => $this->quoteFinishedDate(),
                'year' => date('Y'),
            ])
            ->subject(Lang::get('[Time Sensitive] Please confirm the terms of your offer'));
    }

    /**
     * @param $notifiable
     * @return string
     */
    private function quoteActiveUrl($notifiable): string
    {
        //need token
        $token = encrypt([
            'id' => $notifiable->id,
            'quote_id' => $this->quoteId,
            'deal_id' => $this->dealId,
            'is_active' => true,
        ]);

        return config('app.frontend_url').'/active-quote/'.$token;
    }

    private function quoteFinishedDate(): string
    {
        return Quote::query()
                    ->select('finished_at')
                    ->find($this->quoteId)
                    ->getAttribute('finished_at')
                    ->format('m/d/Y');
    }

    /**
     * @param $notifiable
     * @return string
     */
    private function quoteNotActiveUrl($notifiable): string
    {
        //need token
        $token = encrypt([
            'id' => $notifiable->id,
            'quote_id' => $this->quoteId,
            'deal_id' => $this->dealId,
            'is_active' => false,
        ]);

        return config('app.frontend_url').'/active-quote/'.$token;
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
