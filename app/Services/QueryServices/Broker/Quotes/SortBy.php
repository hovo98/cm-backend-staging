<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Broker\Quotes;

use App\Services\QueryServices\AbstractQueryService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Class SortBy
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class SortBy extends AbstractQueryService
{
    /**
     * Returns the sorted mapped quotes
     *
     * @param  array  $args
     * @return Collection
     */
    public function run(array $args): Collection
    {
        return $this->query($args['sortBy'], $args['query'])->get()->pluck('id');
    }

    /**
     * Returns raw query for the sorted mapped quotes
     *
     * @param  array  $sortBy
     * @param  Builder  $query
     * @return Builder
     */
    public function query(array $sortBy, Builder $query): Builder
    {
        $by = $sortBy['by'];
        $rawBy = strtoupper($by);
        if ($sortBy['sort'] === 'location') {
            $query->join('deals', 'quotes.deal_id', '=', 'deals.id')
                  ->orderBy('deals.data->location->state', $by)
                  ->orderBy('deals.data->location->city', $by)
                  ->orderBy('deals.data->location->sublocality', $by);
        } elseif ($sortBy['sort'] === 'property_type') {
            $query->join('deals', 'quotes.deal_id', '=', 'deals.id')
                  ->orderBy('deals.main_type', $by);
        } elseif ($sortBy['sort'] === 'dollar_amount') {
            $query->orderByRaw('quotes.dollar_amount IS NULL, quotes.dollar_amount '.$rawBy);
        } elseif ($sortBy['sort'] === 'interest_rate') {
            $query->orderByDesc('quotes.interest_swap');
            $query->orderByRaw('quotes.interest_rate IS NULL, quotes.interest_rate '.$rawBy);
            $query->orderByRaw('quotes.interest_rate_spread IS NULL, quotes.interest_rate_spread '.$rawBy);
            $query->orderByRaw('quotes.interest_rate_float IS NULL, quotes.interest_rate_float '.$rawBy);
        } elseif ($sortBy['sort'] === 'rate_term') {
            $query->orderBy('quotes.rate_term', $by);
        } elseif ($sortBy['sort'] === 'origination_fee') {
            $query->orderByRaw('quotes.origination_fee_spread IS NULL, quotes.origination_fee_spread '.$rawBy);
            $query->orderByRaw('quotes.origination_fee IS NULL, quotes.origination_fee '.$rawBy);
        } else {
            $query->orderBy('quotes.finished_at', $by);
        }

        $query->select('quotes.*');

        return $query;
    }
}
