<?php

declare(strict_types=1);

namespace App\Observers;

use App\Events\QuoteChanged;
use App\Quote;
use App\Traits\ModelObserver;

/**
 * Class QuoteObserver
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
class QuoteObserver
{
    use ModelObserver;

    /**
     * Handle the quote "created" event.
     *
     * @param  Quote  $quote
     * @return void
     */
    public function created(Quote $quote)
    {
        $this->fireModelEvent($quote, QuoteChanged::class, 'created');
    }

    /**
     * Handle the quote "created" when quote is published
     *
     * @param  Quote  $quote
     * @return void
     */
    public function createdByUser(Quote $quote)
    {
        $this->fireModelEvent($quote, QuoteChanged::class, 'createdPublished');
    }

    /**
     * Handle the quote "checkActiveQuote" when quote is older than 2weeks
     *
     * @param  Quote  $quote
     * @return void
     */
    public function checkActiveQuote(Quote $quote)
    {
        $this->fireModelEvent($quote, QuoteChanged::class, 'checkActiveQuote');
    }

    /**
     * Handle the quote "quoteNotActive" when lender choose not active
     *
     * @param  Quote  $quote
     * @return void
     */
    public function quoteNotActive(Quote $quote)
    {
        $this->fireModelEvent($quote, QuoteChanged::class, 'quoteNotActive');
    }

    /**
     * Handle the quote "unacceptedQuote" when quote is older than 2weeks
     *
     * @param  Quote  $quote
     * @return void
     */
    public function unacceptedQuote(Quote $quote)
    {
        $this->fireModelEvent($quote, QuoteChanged::class, 'unacceptedQuote');
    }

    /**
     * Handle the quote "updated" event.
     *
     * @param  Quote  $quote
     * @return void
     */
    public function updated(Quote $quote)
    {
        $this->fireModelEvent($quote, QuoteChanged::class, 'updated');
    }

    /**
     * Handle the quote "quoteIsSeen" event.
     *
     * @param  Quote  $quote
     * @return void
     */
    public function quoteIsSeen(Quote $quote)
    {
        $this->fireModelEvent($quote, QuoteChanged::class, 'quoteIsSeen');
    }

    /**
     * Handle the quote "accepted" when broker accept quote for his deal
     *
     * @param  Quote  $quote
     * @return void
     */
    public function acceptedQuote(Quote $quote)
    {
        $this->fireModelEvent($quote, QuoteChanged::class, 'acceptedQuote');
    }

    //Handle the quote "opened" when the broker unaccepts the quote for his deal
    public function declinedQuote(Quote $quote)
    {
        $this->fireModelEvent($quote, QuoteChanged::class, 'declinedQuote');
    }

    /**
     * Handle the quote "deleted" event.
     *
     * @param  Quote  $quote
     * @return void
     */
    public function deleted(Quote $quote)
    {
        $this->fireModelEvent($quote, QuoteChanged::class, 'deleted');
    }

    /**
     * Handle the quote "restored" event.
     *
     * @param  Quote  $quote
     * @return void
     */
    public function restored(Quote $quote)
    {
        $this->fireModelEvent($quote, QuoteChanged::class, 'restored');
    }

    /**
     * Handle the quote "force deleted" event.
     *
     * @param  Quote  $quote
     * @return void
     */
    public function forceDeleted(Quote $quote)
    {
        $this->fireModelEvent($quote, QuoteChanged::class, 'forceDeleted');
    }
}
