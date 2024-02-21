<?php

declare(strict_types=1);

namespace App\Listeners;

use App\BrokerLender;
use App\Deal;
use App\Events\QuoteChanged;
use App\Events\QuoteRejected;
use App\Quote;

/**
 * Class QuoteEventSubscriber
 *
 * @author Hajdi Djukic Grba <hajdi@forwardslashny.com>
 */
class QuoteEventSubscriber
{
    /**
     * Send email to broker when lender quote deal
     *
     * @param $event
     */
    public function sendQuoteDealMail($event)
    {
        /** @var Quote $quote */
        $quote = $event->model();

        if ($event->event() === 'createdPublished') {
            // Call method in Quote
            $quote->sendQuoteDeal();
        }
    }

    /**
     * Go through all the Quotes for the parent Deal and update the deals.unseen_quotes flag
     *
     * @param  QuoteChanged  $event
     */
    public function updateDealByQuotesSeen(QuoteChanged $event)
    {
        /** @var Quote $quote */
        $quote = $event->model();
        $checkEvent = $event->event() === 'createdPublished' || $event->event() === 'quoteIsSeen' || $event->event() === 'acceptedQuote' || $event->event() === 'deleted';

        // Skip if restored or updated event
        if ($checkEvent) {
            /** @var Deal $deal */
            $deal = $quote->deal()->first();
            if (! $deal) {
                return '';
            }
            $acceptedQuotes = $deal->quotes()->where('status', Quote::ACCEPTED)->first();
            $deal->unseen_quotes = $deal->unseenQuotes()->count() > 0;
            if ($acceptedQuotes) {
                $deal->unseen_quotes = false;
            }
            $deal->save();
        }
    }

    /**
     * @param $event
     *
     * If quote is accepted by Broker
     */
    public function acceptedQuoteByBroker($event)
    {
        /** @var Quote $quote */
        $quote = $event->model();

        if ($event->event() === 'acceptedQuote') {
            $lender_id = $quote->user_id;
            $deal = Deal::find($quote->deal_id);
            $broker_id = $deal->user_id;
            $broker_lender = BrokerLender::where('broker_id', $broker_id)->where('lender_id', $lender_id)->first();
            if (! $broker_lender) {
                $broker_lender = new BrokerLender();
                $broker_lender->lender_id = $lender_id;
                $broker_lender->broker_id = $broker_id;
                $broker_lender->save();
            }
            // Call method in Quote
            $quote->sendAcceptedQuote();
        }
    }

    /**
     * @param $event
     *
     * Check if Quote is still active by Lender
     */
    public function checkActiveQuoteByLender($event)
    {
        /** @var Quote $quote */
        $quote = $event->model();

        if ($event->event() === 'checkActiveQuote') {
            // Call method in Quote
            $quote->checkActiveQuoteLender();
        }
    }

    /**
     * @param $event
     *
     * Let know Broker that quote is not active anymore
     */
    public function quoteNotActiveBroker($event)
    {
        /** @var Quote $quote */
        $quote = $event->model();

        if ($event->event() === 'quoteNotActive') {
            // Call method in Quote
            $quote->quoteNotActiveForBroker();
        }
    }

    public function unacceptedQuoteLender($event)
    {
        if ($event->event() === 'unacceptedQuote') {
            QuoteRejected::dispatch($event->model());
        }
    }

    /**
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            \App\Events\QuoteChanged::class,
            'App\Listeners\QuoteEventSubscriber@sendQuoteDealMail'
        );

        $events->listen(
            \App\Events\QuoteChanged::class,
            'App\Listeners\QuoteEventSubscriber@updateDealByQuotesSeen'
        );

        $events->listen(
            \App\Events\QuoteChanged::class,
            'App\Listeners\QuoteEventSubscriber@acceptedQuoteByBroker'
        );

        $events->listen(
            \App\Events\QuoteChanged::class,
            'App\Listeners\QuoteEventSubscriber@checkActiveQuoteByLender'
        );

        $events->listen(
            \App\Events\QuoteChanged::class,
            'App\Listeners\QuoteEventSubscriber@quoteNotActiveBroker'
        );

        $events->listen(
            \App\Events\QuoteChanged::class,
            'App\Listeners\QuoteEventSubscriber@unacceptedQuoteLender'
        );
    }
}
