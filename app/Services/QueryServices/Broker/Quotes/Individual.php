<?php

namespace App\Services\QueryServices\Broker\Quotes;

use App\Events\QuoteChanged;
use App\Lender;
use App\Services\QueryServices\AbstractQueryService;

class Individual extends AbstractQueryService
{
    public function run($args)
    {
        // set quotes to seen

        $lender = Lender::where('role', 'lender')->where('id', $args['lender'])->with(['quotes' => function ($q) use ($args) {
            return $q->where('deal_id', $args['deal'])->where('finished', true);
        }])->first();

        foreach ($lender->quotes as $quote) {
            $quote->seen = true;
            $quote->save();
            event(new QuoteChanged($quote, 'quoteIsSeen'));
        }

        return $lender;
    }
}
