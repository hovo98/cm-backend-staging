<?php

namespace App\Notifications;

use App\Deal;
use App\Quote;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

/**
 * Class ChooseQuoteBroker
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class ChooseQuoteBroker extends Notification
{
    protected $dealId;

    protected $deal;

    protected $acceptedQuote;

    protected $secondAcceptedQuote;

    /**
     * @param $dealId
     */
    public function __construct($dealId)
    {
        $this->dealId = $dealId;
        $this->deal = Deal::query()->find($this->dealId);
        $this->acceptedQuote = $this->deal->quotes()->where('status', Quote::ACCEPTED)->first();
        $this->secondAcceptedQuote = $this->deal->quotes()->where('status', Quote::SECOND_ACCEPTED)->first();
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
            ->view('mail.chooseQuoteBroker', [
                'brokerName' => $notifiable->first_name,
                'dealAddress' => $this->getDealAddress(),
                'urlFirst' => $this->getFirstQuoteUrl($notifiable),
                'firstBankName' => $this->getBankName($this->acceptedQuote),
                'urlSecond' => $this->getSecondQuoteUrl($notifiable),
                'secondBankName' => $this->getBankName($this->secondAcceptedQuote),
                'urlBoth' => $this->getProceedWithBothUrl($notifiable),
                'year' => date('Y'),
            ])
            ->subject(Lang::get('How did it go?'));
    }

    private function getDealAddress(): string
    {
        $location = $this->deal->data['location'];

        return sprintf(
            '%s, %s, %s, %s',
            $location['street_address'],
            $location['city'],
            $location['state'],
            $location['zip_code']
        );
    }

    private function getBankName($quote): string
    {
        $lender = $quote->lender()->first();

        return $lender->getCompany()['company_name'] ?: $lender->getOnlyDomain();
    }

    /**
     * @param $notifiable
     * @return string
     */
    private function getFirstQuoteUrl($notifiable): string
    {
        $token = encrypt([
            'id' => $notifiable->id,
            'quote_id' => $this->acceptedQuote->id,
            'deal_id' => $this->dealId,
            'choose_both' => false,
        ]);

        return config('app.frontend_url').'/choose-quote/'.$token;
    }

    /**
     * @param $notifiable
     * @return string
     */
    private function getSecondQuoteUrl($notifiable): string
    {
        $token = encrypt([
            'id' => $notifiable->id,
            'quote_id' => $this->secondAcceptedQuote->id,
            'deal_id' => $this->dealId,
            'choose_both' => false,
        ]);

        return config('app.frontend_url').'/choose-quote/'.$token;
    }

    /**
     * @param $notifiable
     * @return string
     */
    private function getProceedWithBothUrl($notifiable): string
    {
        $token = encrypt([
            'id' => $notifiable->id,
            'quote_id' => 0,
            'deal_id' => $this->dealId,
            'choose_both' => true,
        ]);

        return config('app.frontend_url').'/choose-quote/'.$token;
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
