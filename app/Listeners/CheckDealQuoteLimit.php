<?php

namespace App\Listeners;

use App\Deal;
use App\Events\QuotePublished;
use App\Quote;

class CheckDealQuoteLimit
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
    public function handle(QuotePublished $event)
    {
        $quote = $event->quote;
        $finishedQuotesCount = Quote::forDeal($quote->deal)->where('finished', true)->count();
        $quote->deal()->update(['quote_limit_reached' => $finishedQuotesCount === Deal::QUOTE_LIMIT]);
    }
}
