<?php

namespace App\Notifications;

use App\AssetTypes;
use App\Broker;
use App\Deal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

/**
 * Class DealCreated
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class DealCreated extends Notification
{
    /**
     * @var array
     */
    protected $deal;

    /**
     * @var string
     */
    protected $loanType;

    /**
     * @var bool
     */
    protected $connected;

    /**
     * DealCreated constructor.
     *
     * @param  array  $deal
     * @param $connected
     */
    public function __construct(array $deal, $connected)
    {
        $this->loanType = ucfirst(strtolower(Deal::LOAN_TYPE[$deal['loan_type']]));
        $this->deal = $deal;
        $this->connected = $connected;
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
        $deal = $this->deal;
        $broker = Broker::query()->find($deal['user_id'], ['first_name', 'last_name']);
        $viewDealUrl = $this->viewDealUrl();
        $loginUrl = $this->loginUrl();
        $ignoreDealUrl = $this->ignoreDealUrl($notifiable, $deal);
        $location = $deal['location']['city'];
        $dollarAmount = $this->formatDollarAmountDeal($deal);

        $assetTypeId = $deal['inducted']['property_type']['asset_types'];
        (count($assetTypeId) > 1 && $assetTypeId[0] === 5)
            ? $title = AssetTypes::find($assetTypeId[1])->title
            : $title = AssetTypes::find($assetTypeId[0])->title;

        $mailMessage = (new MailMessage())->view($this->connected ? 'mail.dealCreatedConnected' : 'mail.dealCreated', [
            'user' => $notifiable->first_name,
            'brokerName' => $broker->name(),
            'viewDealUrl' => $viewDealUrl,
            'ignoreDealUrl' => $ignoreDealUrl,
            'assetType' => $title,
            'loanType' => $this->loanType,
            'location' => $location,
            'linkDealPreferences' => $this->linkDealPreferences(),
            'dollarAmount' => $dollarAmount,
            'year' => date('Y'),
            'loginUrl' => $loginUrl,
        ]);

        if ($this->connected) {
            return $mailMessage
                ->subject(Lang::get('[Time Sensitive] :broker has given you preferred access to a deal', ['broker' => $broker->name()]));
        }

        return $mailMessage
            ->subject(Lang::get('There’s a new deal in :location we know you’d like to meet', ['location' => $location]));
    }

    /**
     * Get the individual Deal URL for the given notifiable.
     *
     * @return string
     */
    public function viewDealUrl()
    {
        return config('app.frontend_url').'/individual-deal/'.$this->deal['id'];
    }

    /**
     * Get the Login URL for the given notifiable.
     *
     * @return string
     */
    public function loginUrl()
    {
        return config('app.frontend_url').'/login';
    }

    /**
     * Get the lender preferences url.
     *
     * @return string
     */
    public function linkDealPreferences()
    {
        return config('app.frontend_url').'/profile-settings/';
    }

    /**
     * @param $notifiable
     * @param $deal
     * @return string
     */
    public function ignoreDealUrl($notifiable, $deal)
    {
        //need token
        $token = encrypt([
            'id' => $notifiable->id,
            'email' => $notifiable->email,
            'deal_id' => $deal['id'],
        ]);

        return config('app.frontend_url').'/ignore-deal/'.$token;
    }

    /**
     * @param $deal
     * @return string
     */
    private function formatDollarAmountDeal($deal): string
    {
        $amount = $deal['inducted']['loan_amount'];
        if ($amount !== 0 || $amount !== '0') {
            $fmt = new \NumberFormatter('en-US', \NumberFormatter::CURRENCY);
            $amountDollar = $fmt->formatCurrency((int) $amount, 'USD');
            $formatAmount = str_replace('.00', '', $amountDollar);
            if ($formatAmount === '$0') {
                return '';
            }

            return $formatAmount;
        }

        return '';
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
