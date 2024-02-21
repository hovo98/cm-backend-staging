<?php

namespace App\Services\MapperServices\Lender\Deals;

use App\DataTransferObjects\Lender\Deals\IndividualQuotes as QuoteMapper;
use App\Interfaces\CollectionMapperService;
use App\Quote;

class IndividualQuotes implements CollectionMapperService
{
    public function map($objs)
    {
        $data['quotes'] = $this->mappedQuotes($objs['quotes']);
        $data['quoteLink'] = $this->generateDraftQuoteLink($objs['dealId'], $objs['userId']);

        return $data;
    }

    protected function mappedQuotes($quotes)
    {
        $mappedQuotes = [];
        foreach ($quotes as $quote) {
            $quoteMapper = new QuoteMapper($quote->id);
            $mappedQuote = $quoteMapper->mapFromEloquent();
            $mappedQuotes[] = $mappedQuote;
        }

        return $mappedQuotes;
    }

    /**
     * @param $dealId
     * @param $userId
     * @return string generate link for quote draft
     */
    protected function generateDraftQuoteLink($dealId, $userId)
    {
        // if draft quote exists
        $quote = Quote::where('user_id', $userId)->where('finished', false)->where('deal_id', $dealId)->first();
        if ($quote) {
            // get last step
            $json = json_decode($quote['lastStepStatus'], true);
            $history = end($json['history']);

            // generate link
            return config('app.frontend_url').'/create-quote/'.$dealId.'/'.$history.'/'.$quote->id;
        }

        return '';
    }
}
