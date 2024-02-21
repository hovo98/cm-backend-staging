<?php

declare(strict_types=1);

namespace App;

use App\Mail\ErrorEmail;
use App\Mail\QuoteEmails;
use App\Notifications\CheckActiveQuoteLender as CheckActiveQuoteLenderNotification;
use App\Notifications\QuoteAccepted as QuoteAcceptedNotification;
use App\Notifications\QuoteDeal as QuoteNotification;
use App\Notifications\QuoteNotActiveBroker as QuoteNotActiveBrokerNotification;
use App\Notifications\UnacceptedQuoteLender as UnacceptedQuoteLenderNotification;
use App\Traits\CascadeRestore;
use App\Traits\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Mail;

/**
 * Class Quote
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class Quote extends Model
{
    use HasFactory;
    use SoftDeletes;
    use CascadeSoftDeletes;
    use CascadeRestore;

    public const INTEREST_RATE_TYPE = [
        0 => 'UNDEFINED',
        1 => 'FIXED',
        2 => 'FLOATING',
        3 => 'FLOATING_TO_BE_FIXED_BEFORE_CLOSING',
        4 => 'SWAP',
    ];

    public const QUOTE_STATUS = [
        1 => 'OPENED',
        2 => 'ACCEPTED',
        3 => 'NOT_AVAILABLE',
        4 => 'INACTIVE',
        5 => 'PENDING',
        6 => 'SECOND_ACCEPTED',
        7 => 'DECLINED',
    ];

    public const DECISION_OPTION_TYPE = [
        0 => 'UNDEFINED',
        1 => 'YES',
        2 => 'NO',
    ];

    public const RECOURSE_TYPE = [
        0 => 'UNDEFINED',
        1 => 'FULL_RECOURSE',
        2 => 'PARTIAL_RECOURSE',
        3 => 'BAD_BOY_CARVEOUTS',
    ];

    public const RECOURSE_TYPE_PURCHASE = [
        0 => 'UNDEFINED',
        1 => 'FULL_RECOURSE',
        2 => 'PARTIAL_RECOURSE',
        3 => 'BURNOFF_RECOURSE',
        4 => 'SPRINGING_RECOURSE',
        5 => 'BAD_BOY_CARVEOUTS',
    ];

    /**
     * Constants for Quote status
     */
    public const OPENED = 1;

    public const ACCEPTED = 2;

    public const NOT_AVAILABLE = 3;

    public const INACTIVE = 4;

    public const PENDING = 5;

    public const SECOND_ACCEPTED = 6;

    public const DECLINED = 7;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'finished_at' => 'datetime',
        'data' => 'array',    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'data', 'user_id', 'deal_id', 'finished', 'dollar_amount', 'interest_rate', 'rate_term', 'origination_fee', 'finished_at',
        'status', 'origination_fee_spread', 'interest_rate_spread', 'interest_rate_float', 'interest_swap', 'unaccept_message', 'deleted_by',
    ];

    public function scopeForDeal($query, Deal $deal)
    {
        return $query->where('deal_id', $deal->id);
    }

    /**
     * Column for Soft delete
     */
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lender()
    {
        return $this->belongsTo(Lender::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lenderWithTrashed()
    {
        return $this->belongsTo(Lender::class, 'user_id')->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    /**
     * Send Broker email when new Quote is created by Lender
     */
    public function sendQuoteDeal()
    {
        $loan_type = $this->deal->data['loan_type'];
        $quoteData = $this->data;
        $message = $quoteData['message'];
        $streetName = $this->deal->data['location']['street_address'];
        $broker = User::findOrFail($this->deal->user_id);
        $lender = User::findOrFail($this->user_id);

        try {
            $broker->notify(new QuoteNotification($loan_type, $lender->name(), $this->id, $this->deal->id, $this->user_id, $message, $streetName));
            $mail = Mail::send(new QuoteEmails($this, $this->deal, $broker, $lender));
        } catch (\Throwable $exception) {
            Mail::send(new ErrorEmail($broker->email, 'Send Broker email when new Quote is created by Lender', $exception));
        }
    }

    /**
     * Send email to broker and lender that quote is accepted by broker
     */
    public function sendAcceptedQuote()
    {
        $broker = User::where('id', $this->deal->user_id)->first();
        $lender = User::where('id', $this->user_id)->first();

        $users = collect([$broker, $lender]);
        $users->each(function ($user) use ($broker, $lender) {
            try {
                $user->notify(new QuoteAcceptedNotification($this, $this->deal, $broker, $lender));
            } catch (\Throwable $exception) {
                Mail::send(new ErrorEmail($broker->email.', '.$lender->email, 'Send email to broker and lender that quote is accepted by broker', $exception));
            }
        });
    }

    /**
     * Send email to Lender if quote is older than 2 weeks and Broker wants to accepted
     */
    public function checkActiveQuoteLender()
    {
        $lender = User::where('id', $this->user_id)->first();
        $broker = User::where('id', $this->deal->user_id)->first();
        try {
            $lender->notify(new CheckActiveQuoteLenderNotification($broker->name(), $this->id, $this->deal_id));
        } catch (\Throwable $exception) {
            Mail::send(new ErrorEmail($lender->email, 'Send email to Lender if quote is older than 2 weeks and Broker wants to accepted', $exception));
        }
    }

    /**
     * Send email to Broker that Lender said that quote is no longer active
     */
    public function quoteNotActiveForBroker()
    {
        $broker = User::where('id', $this->deal->user_id)->first();
        try {
            $broker->notify(new QuoteNotActiveBrokerNotification($this->id, $this->deal_id, $this->user_id));
        } catch (\Throwable $exception) {
            Mail::send(new ErrorEmail($broker->email, 'Send email to Broker that Lender said that quote is no longer active', $exception));
        }
    }

    /**
     * Send email to Lender that Broker choose other quote
     */
    public function unacceptedQuoteForLender()
    {
        $lender = User::where('id', $this->user_id)->first();
        try {
            $lender->notify(new UnacceptedQuoteLenderNotification($this->deal_id));
        } catch (\Throwable $exception) {
            Mail::send(new ErrorEmail($lender->email, 'Send email to Lender that Broker choose other quote', $exception));
        }
    }

    /**
     * Get quote button status
     * OPENED || ACCEPTED || NOT_AVAILABLE || INACTIVE || PENDING || SECOND_ACCEPTED || DECLINED
     */
    public function getQuoteStatusButton(): bool
    {
        $deal = Deal::find($this->deal_id);
        $pendingQuotes = $deal->quotes()->where('status', Quote::PENDING)->first();
        $acceptedQuotes = $deal->quotes()->where('status', Quote::ACCEPTED)->first();

        //If this quote is not opened
        if ($this->status !== Quote::OPENED) {
            return false;
        }
        //If deal accepted second quote and deal is not open
        if ($deal->second_quote_accepted_at && $deal->termsheet !== Deal::OPEN) {
            return false;
        }
        //If there is pending and accepted quote
        if ($pendingQuotes && $acceptedQuotes) {
            return false;
        }
        //If deal has one accepted quote or none
        if (! $deal->second_quote_accepted_at) {
            return true;
        }

        return false;
    }

    public function isOlderThanTwoWeeks(): bool
    {
        return ! $this->finished_at->addWeeks(2)->isFuture();
    }

    /**
     * @return bool
     * Check if deal has accepted quote
     */
    public function isSecondAccept(): bool
    {
        $deal = $this->deal()->first();
        $quotesAccepted = $deal->quotes()->where('status', Quote::ACCEPTED)->count();

        return $quotesAccepted === 1;
    }
}
