<?php

namespace App\Services\QueryServices\Lender\Deals;

use App\Quote;
use App\Services\QueryServices\AbstractQueryService;

class IndividualQuotes extends AbstractQueryService
{
    public function run(array $args)
    {
        return [
            'quotes' => Quote::where('user_id', $args['user'])->where('finished', true)->where('deal_id', $args['deal'])->get(),
            'dealId' => $args['deal'],
            'userId' => $args['user'],
        ];
    }
}
