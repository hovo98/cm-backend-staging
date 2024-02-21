<?php

namespace App\Notifications;

use App\Deal;
use App\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

/**
 * Class QuoteDeal
 *
 * @author Hajdi Djukic Grba <hajdi@forwardslashny.com>
 */
class QuoteDeal extends Notification
{
    /**
     * @var string
     */
    private $loan_type;

    /**
     * @var string
     */
    private $lender;

    /**
     * @var int
     */
    private $quote_id;

    /**
     * @var int
     */
    private $deal_id;

    /**
     * @var int
     */
    private $lender_id;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $streetName;

    /**
     * Create a new notification instance.
     *
     * @param  int  $loan_type
     * @param  string  $lender
     * @param  int  $quote_id
     * @param  int  $deal_id
     * @param  int  $lender_id
     * @param  string  $message
     * @param  string  $streetName
     */
    public function __construct(int $loan_type, string $lender, int $quote_id, int $deal_id, int $lender_id, string $message, string $streetName)
    {
        $this->loan_type = ucfirst(strtolower(Deal::LOAN_TYPE[$loan_type]));
        $this->lender = $lender;
        $this->quote_id = $quote_id;
        $this->deal_id = $deal_id;
        $this->lender_id = $lender_id;
        $this->message = $message;
        $this->streetName = $streetName;
    }

    /**
     * The callback that should be used to build the mail message.
     *
     * @var \Closure|null
     */
    public static $toMailCallback;

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $quoteDealUrl = $this->quoteDealUrl($this->deal_id, $this->lender_id);
        $lender = User::find($this->lender_id);
        $deal = Deal::find($this->deal_id);

        $bankName = $this->getBankName($lender, $deal);

        $mailMessage = new MailMessage();
        $mailMessage->view = 'mail.quoteDeal';
        // $mailMessage->bcc = [config('mail.bcc')];

        $bcc = explode(',', config('mail.bcc'));

        $mailMessage->viewData = [
            'user' => $notifiable,
            'url' => $quoteDealUrl,
            'loan_type' => $this->loan_type,
            'lender' => $this->lender,
            'year' => date('Y'),
            'quoteMsg' => $this->message,
            'streetName' => $this->streetName,
            'bankName' => $bankName,
        ];

        return $mailMessage
            ->subject(Lang::get('New quote from '. $bankName))
            ->bcc($bcc);
    }

    public function getBankName(User $lender, Deal $deal): string
    {
        if ($deal->isPremium()) {
            return $lender->getCompanyNameFromMetasOrFromCompanyRelationship();
        }
        return 'a Lender';
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  int  $deal_id
     * @param  int  $lender_id
     * @return string
     */
    public function quoteDealUrl(int $deal_id, int $lender_id)
    {
        return config('app.frontend_url').'/individual-quote-broker/'.$deal_id.'/'.$lender_id;
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
