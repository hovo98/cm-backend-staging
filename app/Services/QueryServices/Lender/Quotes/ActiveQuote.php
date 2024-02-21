<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Quotes;

use App\Deal;
use App\Events\QuoteChanged;
use App\Quote;
use App\Services\QueryServices\AbstractQueryService;

/**
 * Class ActiveQuote
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class ActiveQuote extends AbstractQueryService
{
    public function run($args)
    {
        $lender = $args['lender'];
        $dealId = $args['dealId'];
        $is_active = $args['is_active'];

        //If quote doesn't exists or quote doesnt belong to this user
        $quote = Quote::find($args['quoteId']);
        if (! $quote || $quote->user_id !== $lender->id) {
            return [
                'status' => false,
                'message' => 'The Quote doesn\'t exist or this quote doesn\'t belong to this user.',
            ];
        }

        //Check if quote belongs to deal id
        if ($quote->deal_id !== $dealId) {
            return [
                'status' => false,
                'message' => 'This quote doesn\'t belong to this deal.',
            ];
        }
        //Check if deal exist
        $deal = Deal::find($quote->deal_id);
        if (! $deal) {
            return [
                'status' => false,
                'message' => 'The Deal doesn\'t exist.',
            ];
        }

        if (! $is_active) {
            $quote->update(['status' => Quote::NOT_AVAILABLE]);
            event(new QuoteChanged($quote, 'quoteNotActive'));

            return [
                'status' => false,
                'message' => 'Your quote is no longer available.',
            ];
        }

        // Update deal and quote status
        $quotesAccepted = $deal->quotes()->where('status', Quote::ACCEPTED)->count();

        //If this first quote change deal status
        if (! $deal->second_quote_accepted_at) {
            $quote->update(['status' => Quote::ACCEPTED]);
            $deal->termsheet = Deal::QUOTE_ACCEPTED;
        }
        //If it's second quote save time when it's accepted
        if ($quotesAccepted === 1) {
            $quote->update(['status' => Quote::SECOND_ACCEPTED]);
            $deal->second_quote_accepted_at = now();
        }
        $deal->save();
        event(new QuoteChanged($quote, 'acceptedQuote'));

        return [
            'status' => true,
            'message' => 'The Quote is active and accepted.',
        ];
    }
}
