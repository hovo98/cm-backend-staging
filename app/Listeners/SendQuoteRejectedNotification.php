<?php

namespace App\Listeners;

use App\Events\QuoteRejected;
use App\Mail\ErrorEmail;
use App\Notifications\UnacceptedQuoteLender as UnacceptedQuoteLenderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendQuoteRejectedNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  QuoteRejected  $event
     * @return void
     */
    public function handle(QuoteRejected $event)
    {
        $quote = $event->quote;
        $lender = $quote->lender;

        try {
            $lender->notify(new UnacceptedQuoteLenderNotification($quote->deal_id));
        } catch (\Throwable $exception) {
            Mail::send(new ErrorEmail($lender->email, 'Send email to Lender that Broker choose other quote', $exception));
        }
    }
}
