<?php

namespace App\Services\QueryServices\Lender\Deals;

use App\Deal;
use App\Services\QueryServices\AbstractQueryService;

class DealForQuoteCreateForm extends AbstractQueryService
{
    public function run(array $args)
    {
        $deal = Deal::find($args['id']);

        return $deal;
    }
}
