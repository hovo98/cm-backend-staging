<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Deals;

use App\Services\QueryServices\AbstractQueryService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Class SortByDeals
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class SortByDeals extends AbstractQueryService
{
    /**
     * @param  array  $args
     *
     *  SORT BY
     * 'address'  - metas.location.sublocality - location
     * 'date posted' - updated_at - date_posted
     * 'property_type' - existing.propertyType && property_type (if 0 than first) - property_type
     *  loan_amount
     *  loan_type
     * @return Collection
     */
    public function run(array $args): Collection
    {
        return $this->query($args['sortBy'], $args['query'])->get();
    }

    public function query(array $sortBy, Builder $query): Builder
    {
        $by = $sortBy['by'];
        if ($sortBy['sort'] === 'location') {
            $query->orderBy('deals.location', $by);
        } elseif ($sortBy['sort'] === 'date_posted') {
            $query->orderBy('deals.finished_at', $by);
        } elseif ($sortBy['sort'] === 'property_type') {
            $query->orderBy('deals.main_type', $by);
        } elseif ($sortBy['sort'] === 'loan_type') {
            $query->orderBy('deals.data->inducted->loan_type', $by);
        } elseif ($sortBy['sort'] === 'loan_amount') {
            $query->orderBy('deals.dollar_amount', $by);
        } elseif ($sortBy['sort'] === 'updated_at') {
            $query->orderBy('deals.updated_at', $by);
        } else {
            $query->orderBy('deals.finished_at', $by);
        }

        return $query;
    }
}
