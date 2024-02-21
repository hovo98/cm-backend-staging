<?php

namespace App\Services\QueryServices\Broker\Deals;

use App\Deal;
use App\Services\QueryServices\AbstractQueryService;

class SetTermsheet extends AbstractQueryService
{
    public function run(array $args): bool
    {
        try {
            $deal = Deal::find($args['deal']);

            if ($deal->termsheet === Deal::OPEN || $args['term'] === Deal::OPEN) {
                return false;
            }

            $deal->termsheet = $args['term'];
            $deal->save();
        } catch (\Exception $e) {
            dd($e->getMessage());

            return false;
        }

        return true;
    }
}
