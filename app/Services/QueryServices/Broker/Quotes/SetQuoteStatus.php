<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Broker\Quotes;

use App\Deal;
use App\Events\QuoteChanged;
use App\Quote;
use App\Services\QueryServices\AbstractQueryService;
use Illuminate\Support\Carbon;

/**
 * Class SetQuoteStatus
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class SetQuoteStatus extends AbstractQueryService
{
    public function run($args)
    {
        $user = $args['broker'];
        $statusType = $args['statusType'];
        $flag = false;
        $deal_termsheet_status = false;
        $quotes = [];
        $message = '';

        if (isset($args['unacceptMessage'])) {
            $quote = Quote::find($args['quoteId']);
            $deal = Deal::find($quote->deal_id);

            $quotesAccepted = $deal->quotes()->where('status', Quote::SECOND_ACCEPTED)->count();

            if ($quotesAccepted === 1) {
                $secondQuoteObj = $deal->quotes()->get();
                foreach ($secondQuoteObj as $quoteObj) {
                    if ($quote->id === $quoteObj->id) {
                        $updateFields = [
                            'status' => Quote::OPENED,
                            'unaccept_message' => $args['unacceptMessage'],
                        ];
                        $quoteObj->update($updateFields);
                    } else {
                        if ($quoteObj->status === Quote::SECOND_ACCEPTED) {
                            $updateFields = [
                                'status' => Quote::ACCEPTED,
                            ];
                            $quoteObj->update($updateFields);
                        }
                    }
                }
            } else {
                $updateFields = [
                    'status' => Quote::OPENED,
                    'unaccept_message' => $args['unacceptMessage'],
                ];
                $quote->update($updateFields);
                $deal->termsheet = Deal::OPEN;
            }

            $deal->update(['second_quote_accepted_at' => null]);
            $deal->save();

            event(new QuoteChanged($quote, 'declinedQuote'));

            $flag = true;
            $deal_termsheet_status = true;

            //Get the button status for the quote
            $quotes = $this->getQuotesButtonStatus($deal->quotes()->get());

            return [
                'quotes' => $quotes,
                'status' => $flag,
                'deal_termsheet_status' => $deal_termsheet_status,
                'message' => $args['unacceptMessage'],
                'anyQuoteAccepted' => $this->chechIfQuoteAccepted($deal),
            ];
        }

        //If quote doesn't exists or if status is not accept return
        $quote = Quote::find($args['quoteId']);
        //Remove the unaccept message from database
        $quote->update(['unaccept_message' => null]);

        $deal = $quote->deal;

        if (! $quote || $statusType !== Quote::ACCEPTED) {
            return [
                'status' => $flag,
                'deal_termsheet_status' => $deal_termsheet_status,
                'quotes' => $quotes,
                'message' => 'The Quote doesn\'t exist or status is not Accepted.',
                'anyQuoteAccepted' => $this->chechIfQuoteAccepted($deal),
            ];
        }

        //Get deal and check if broker is user that is created by that broker
        $deal = Deal::find($quote->deal_id);
        if (! $deal || $deal->user_id !== $user->id) {
            return [
                'status' => $flag,
                'deal_termsheet_status' => $deal_termsheet_status,
                'quotes' => $quotes,
                'message' => 'This deal doesn\'t belong to this user.',
                'anyQuoteAccepted' => $this->chechIfQuoteAccepted($deal),
            ];
        }

        //Check if deal has accepted second quote
        if ($deal->termsheet !== Deal::OPEN && $deal->second_quote_accepted_at) {
            return [
                'status' => $flag,
                'deal_termsheet_status' => $deal_termsheet_status,
                'quotes' => $quotes,
                'message' => 'You cannot accept more than 2 quotes per deal',
                'anyQuoteAccepted' => $this->chechIfQuoteAccepted($deal),
            ];
        }

        //If quote status is already accepted return
        if ($quote->status === Quote::ACCEPTED) {
            return [
                'status' => $flag,
                'deal_termsheet_status' => $deal_termsheet_status,
                'quotes' => $quotes,
                'message' => 'You\'ve already accepted this quote',
                'anyQuoteAccepted' => $this->chechIfQuoteAccepted($deal),
            ];
        }
        $quotesAccepted = $deal->quotes()->where('status', Quote::ACCEPTED)->count();
        //Check if second quote is older than two weeks
        if ($quotesAccepted === 1 && Carbon::parse($quote->finished_at)->diffInDays(now(), false) >= 14) {
            $quote->update(['status' => Quote::PENDING]);
            event(new QuoteChanged($quote, 'checkActiveQuote'));

            return [
                'status' => $flag,
                'deal_termsheet_status' => $deal_termsheet_status,
                'quotes' => $quotes = $this->getQuotesButtonStatus($deal->quotes()->get()),
                'message' => 'Looks like this quote is from a while ago. It will be accepted once we confirm it\'s still valid.',
                'anyQuoteAccepted' => $this->chechIfQuoteAccepted($deal),
            ];
        }
        $status = Quote::ACCEPTED;
        if ($quotesAccepted === 1) {
            $status = Quote::SECOND_ACCEPTED;
        }
        // Update deal and quote status
        $quote->update(['status' => $status]);

        //If this first quote change deal status
        if (! $deal->second_quote_accepted_at) {
            $deal->termsheet = Deal::QUOTE_ACCEPTED;
        }
        //If it's second quote save time when it's accepted
        if ($status === Quote::SECOND_ACCEPTED) {
            $deal->second_quote_accepted_at = now();
        }
        $deal->save();
        $quotes = $this->getQuotesButtonStatus($deal->quotes()->get());
        event(new QuoteChanged($quote, 'acceptedQuote'));
        $flag = true;
        $deal_termsheet_status = true;

        return [
            'quotes' => $quotes,
            'status' => $flag,
            'deal_termsheet_status' => $deal_termsheet_status,
            'message' => $message,
            'anyQuoteAccepted' => $this->chechIfQuoteAccepted($deal),
        ];
    }

    private function chechIfQuoteAccepted($deal)
    {
        $countQuoteAccepted = $deal->quotes()->where('status', Quote::ACCEPTED)->count();
        if ($countQuoteAccepted > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param $dealQuotes
     * @return array
     */
    private function getQuotesButtonStatus($dealQuotes): array
    {
        $quotes = [];
        foreach ($dealQuotes as $dealQuote) {
            $button = $dealQuote->getQuoteStatusButton();
            $quotes[] = [
                'id' => $dealQuote->id,
                'status' => $dealQuote->status,
                'button' => $button,
            ];
        }

        return $quotes;
    }
}
