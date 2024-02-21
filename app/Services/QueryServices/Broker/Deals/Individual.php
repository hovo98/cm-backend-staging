<?php

namespace App\Services\QueryServices\Broker\Deals;

use App\Lender;
use App\Services\QueryServices\AbstractQueryService;

class Individual extends AbstractQueryService
{
    public function run($args)
    {
        return Lender::where('role', 'lender')->with(['quotes' => function ($q) use ($args) {
            return $q->where('deal_id', $args['deal'])->where('finished', true);
        }])->get();
    }
}
