<?php

namespace App\Mail;

use App\Broker;
use App\DataTransferObjects\Quote\IndividualAllData;
use App\Deal;
use App\Lender;
use App\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuoteEmails extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @var Quote
     */
    public $quote;

    /**
     * @var Deal
     */
    public $deal;

    /**
     * @var Broker
     */
    public $broker;

    /**
     * @var Lender
     */
    public $lender;

    /**
     * DealCreated constructor.
     *
     * @param $quote
     * @param $deal
     * @param $broker
     * @param $lender
     */
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($quote, $deal, $broker, $lender)
    {
        $this->quote = $quote;
        $this->deal = $deal;
        $this->broker = $broker;
        $this->lender = $lender;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $dealData = $this->prepareDealData($this->deal);
        $quoteData = $this->prepareQuoteData($this->quote);

        Log::debug($quoteData);
        $data = [
            'deal' => $dealData,
            'address' => $dealData['address'],
            'loan_type' => $dealData['loan_type'],
            'main_type' => $dealData['main_type'],
            'quote' => $quoteData,
            'quote2' => $quoteData,
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

        $mail = $this->from('no-reply@financelobby.com', 'Finance Lobby')
            // ->to('hello@financelobby.com')
            ->to(explode(',', config('mail.to')))
            ->subject($this->lender->name().' submitted a quote on a deal.')
            // ->subject('New quote give on deal ' . $dealData['address'])
            ->view('mail.quote-created', $data);

        return $mail;
    }

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
            'sponsor_name' => ucfirst($deal->sponsor_name),
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
