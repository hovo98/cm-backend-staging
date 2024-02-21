<?php

namespace App\Services\QueryServices\Lender\Quotes;

use App\Lender;
use App\Quote;
use App\Services\QueryServices\AbstractQueryService;

class Individual extends AbstractQueryService
{
    public function run(array $args)
    {
        //        return Lender::whereHas('quotes', function($query) use ($args) {
        //            return $query->where('deal_id', $args['deal'])->where('user_id', $args['user']);
        //        })->where('finished', true)->first();

        return Quote::find($args['quote']);
    }
}
