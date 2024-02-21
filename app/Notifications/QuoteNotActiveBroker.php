<?php

namespace App\Notifications;

use App\Quote;
use App\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

/**
 * Class QuoteNotActiveBroker
 *
 *  @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class QuoteNotActiveBroker extends Notification
{
    /**
     * @var int
     */
    protected $dealId;

    protected $lenderId;

    protected $quoteId;

    /**
     * @param $quoteId
     * @param $dealId
     * @param $lenderId
     */
    public function __construct($quoteId, $dealId, $lenderId)
    {
        $this->quoteId = $quoteId;
        $this->dealId = $dealId;
        $this->lenderId = $lenderId;
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
        $userLender = User::find($this->lenderId);
        $company = $userLender->getCompanyNameFromMetasOrFromCompanyRelationship();

        return (new MailMessage())
            ->view('mail.quoteNotActiveBroker', [
                'brokerName' => $notifiable->first_name,
                'bankName' => empty($company) ? 'the quote owner' : $company,
                'quoteFinishedDate' => $this->quoteFinishedDate(),
                'alternateQuoteUrl' => $this->alternateQuoteUrl(),
                'createDealUrl' => $this->createDealUrl(),
                'year' => date('Y'),
            ])
            ->subject(Lang::get('Offer :fromBankName is no longer available', [
                'fromBankName' => empty($company) ? '' : 'from '.$company,
            ]));
    }

    /**
     * @return string
     */
    private function quoteFinishedDate(): string
    {
        return Quote::query()
                    ->select('finished_at')
                    ->find($this->quoteId)
                    ->getAttribute('finished_at')
                    ->format('m/d/Y');
    }

    /**
     * @return string
     */
    private function createDealUrl(): string
    {
        return config('app.frontend_url').'/create-deal';
    }

    /**
     * @return string
     */
    private function alternateQuoteUrl(): string
    {
        return config('app.frontend_url').'/individual-quote-broker/'.$this->dealId.'/'.$this->lenderId;
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
