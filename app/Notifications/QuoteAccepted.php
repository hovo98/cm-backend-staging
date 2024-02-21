<?php

namespace App\Notifications;

use App\Broker;
use App\DataTransferObjects\Quote\IndividualAllData;
use App\Deal;
use App\Lender;
use App\Quote;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

/**
 * Class QuoteAccepted
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class QuoteAccepted extends Notification
{
    /**
     * @var Quote
     */
    protected $quote;

    /**
     * @var Deal
     */
    protected $deal;

    /**
     * @var Broker
     */
    protected $broker;

    /**
     * @var Lender
     */
    protected $lender;

    /**
     * DealCreated constructor.
     *
     * @param $quote
     * @param $deal
     * @param $broker
     * @param $lender
     */
    public function __construct($quote, $deal, $broker, $lender)
    {
        $this->quote = $quote;
        $this->deal = $deal;
        $this->broker = $broker;
        $this->lender = $lender;
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
        if ($notifiable->role === 'lender') {
            $cc = $this->broker->email;
        } else {
            $cc = $this->lender->email;
        }

        $dealData = $this->prepareDealData($this->deal);
        $quoteData = $this->prepareQuoteData($this->quote);
        $viewDealUrl = $this->dealUrl($notifiable, $this->deal);

        $mailMessage = new MailMessage();

        $mailMessage->view = 'mail.quoteAccepted';
        $mailMessage->viewData = [
            'dealUrl' => $viewDealUrl,
            'deal' => $dealData,
            'quote' => $quoteData,
            'broker' => $this->broker->name(),
            'lender' => $this->lender->name(),
            'quoteAmount' => $this->getLoanAmountQuote($quoteData),
            'lenderName' => $this->nameAndBank($this->lender),
            'lenderEmail' => $this->lender->email,
            'lenderPhone' => $this->lender->phone ?? '',
            'brokerName' => $this->nameAndBank($this->broker),
            'brokerEmail' => $this->broker->email,
            'brokerPhone' => $this->broker->phone ?? '',
            'year' => date('Y'),
        ];

        $bcc = explode(',', config('mail.bcc'));

        return $mailMessage
            ->subject(Lang::get('Congratulations - you\'ve got yourself a deal!'))
            ->replyTo($cc)
            ->bcc($bcc);
    }

    /**
     * @param $notifiable
     * @param $deal
     * @return string
     */
    public function dealUrl($notifiable, $deal)
    {
        $partLink = 'individual-deal';
        if ($notifiable->role === 'broker') {
            $partLink .= '-broker';
        }

        return config('app.frontend_url').'/'.$partLink.'/'.$deal->id;
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
     * @param $deal
     * @return array
     * Prepare deal data for email
     */
    private function prepareDealData($deal): array
    {
        $mappedDeal = $deal->mappedDeal();
        $main_type = DB::table('asset_types')->where('asset_types.id', $deal->main_type)->select('asset_types.title')->first();
        $is_city = $mappedDeal['location']['city'] ? $mappedDeal['location']['city'] : $mappedDeal['location']['sublocality'];

        return [
            'neighbourhood' => $is_city,
            'address' => $mappedDeal['location']['street_address'].', '.$is_city.', '.$mappedDeal['location']['state'].', '.$mappedDeal['location']['zip_code'],
            'id' => $deal->id,
            'main_type' => $main_type->title,
            'sponsor_name' => ucwords($deal->sponsor_name),
            'loan_type' => ucfirst(strtolower(Deal::LOAN_TYPE[$mappedDeal['loan_type']])),
        ];
    }

    /**
     * @param $quote
     * @return array
     * Prepare quote data for eamil
     *
     * @throws \Exception
     */
    private function prepareQuoteData($quote)
    {
        $quoteMapper = new IndividualAllData($quote->id);

        return $quoteMapper->mapFromEloquent();
    }

    private function getLoanAmountQuote($quoteData)
    {
        $dollarAmountConst = $quoteData['Dollar Amount'];
        $ltvConst = $quoteData['Loan to value ratio'];
        $ltcConst = $quoteData['Loan to cost ratio'];
        $dollarAmountPurchase = $quoteData['Offer dollar amount'];
        $ltvPurchase = $quoteData['Loan To Value Ratio '];
        $ltcPurchase = $quoteData['Loan To Cost Ratio '];
        if ($dollarAmountConst) {
            return $dollarAmountConst;
        } elseif ($ltvConst) {
            return $ltvConst.' LTV';
        } elseif ($ltcConst) {
            return $ltcConst.' LTC';
        } elseif ($dollarAmountPurchase) {
            return $dollarAmountPurchase;
        } elseif ($ltvPurchase) {
            return $ltvPurchase.' LTV';
        } elseif ($ltcPurchase) {
            return $ltcPurchase.' LTC';
        } else {
            return '';
        }
    }

    private function nameAndBank($user)
    {
        $name = $user->name();
        $company = $user->getCompanyNameFromMetasOrFromCompanyRelationship();

        if ($company) {
            $name .= ' of '.$company;
        }

        return $name;
    }
}
