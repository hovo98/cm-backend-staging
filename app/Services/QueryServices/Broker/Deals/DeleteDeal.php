<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Broker\Deals;

use App\Deal;
use App\Quote;
use App\Services\QueryServices\AbstractQueryService;

/**
 * Class DeleteDeal
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class DeleteDeal extends AbstractQueryService
{
    public function run($args)
    {
        foreach ($args['deals'] as $dealID) {
            $deal = Deal::find($dealID['id']);
            if ($deal && $deal->user_id === $args['broker']) {
                $quotes = Quote::where('deal_id', $deal->id)->get();

                foreach ($quotes as $quote) {
                    $quote->deleted_by = 2;
                    $quote->save();
                }

                $deal->delete();
            }
        }

        return true;
    }
}
