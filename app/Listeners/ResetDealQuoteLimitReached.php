<?php

namespace App\Listeners;

use App\Deal;
use App\Events\QuoteRejected;

class ResetDealQuoteLimitReached
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
     * @param  object  $event
     * @return void
     */
    public function handle(QuoteRejected $event)
    {
        /** @var Deal $deal */
        $deal = $event->quote->deal;
        if ($deal->quoteLimitReached()) {
            $deal->quote_limit_reached = false;
            $deal->saveQuietly(); //we might not want to trigger the event again.
        }
    }
}
