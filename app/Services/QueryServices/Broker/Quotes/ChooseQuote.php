<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Broker\Quotes;

use App\Deal;
use App\Events\QuoteChanged;
use App\Quote;
use App\Services\QueryServices\AbstractQueryService;

/**
 * Class ChooseQuote
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class ChooseQuote extends AbstractQueryService
{
    public function run($args)
    {
        //If Broker choose to proceed with both
        if ($args['chooseBoth']) { //Should I save in database????
            return [
                'status' => true,
                'message' => 'Still proceeding with both.',
            ];
        }

        //If quote doesn't exists
        $quote = Quote::find($args['quoteId']);
        if (! $quote) {
            return [
                'status' => false,
                'message' => 'The Quote doesn\'t exist.',
            ];
        }

        //If deal doesn't exists
        $deal = Deal::find($args['dealId']);
        if (! $deal) {
            return [
                'status' => false,
                'message' => 'The Deal doesn\'t exist.',
            ];
        }
        //Change status for quote that is no longer accepted and sent lender email
        $acceptedQuote = $deal->quotes()->where('status', Quote::ACCEPTED)->first();
        $secondAcceptedQuote = $deal->quotes()->where('status', Quote::SECOND_ACCEPTED)->first();

        if ($acceptedQuote->id === $args['quoteId']) {
            $secondAcceptedQuote->update(['status' => Quote::DECLINED]);
            $acceptedQuote->update(['status' => Quote::ACCEPTED]);
            event(new QuoteChanged($secondAcceptedQuote, 'unacceptedQuote'));
        }
        if ($secondAcceptedQuote->id === $args['quoteId']) {
            $acceptedQuote->update(['status' => Quote::DECLINED]);
            $secondAcceptedQuote->update(['status' => Quote::ACCEPTED]);
            event(new QuoteChanged($acceptedQuote, 'unacceptedQuote'));
        }

        return [
            'status' => true,
            'message' => 'Thanks for keeping us in the loop. Good luck!',
        ];
    }
}
