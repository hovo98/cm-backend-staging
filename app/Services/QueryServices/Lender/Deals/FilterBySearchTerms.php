<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Deals;

use App\Services\QueryServices\AbstractQueryService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Class SearchByFilterBySearchTerms
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class FilterBySearchTerms extends AbstractQueryService
{
    /**
     * Returns the Deal IDs that are matched with search term by sponsor name or location
     *
     * @param  array  $args
     * @return Collection
     */
    public function run(array $args): Collection
    {
        return $this->query($args['searchTerms'], $args['query'])->get()->pluck('id');
    }

    /**
     * Returns raw query for the Deal IDs that are matched with search term by sponsor name or location
     *
     * @param  string  $searchTerms
     * @param  Builder  $query
     * @return Builder
     */
    public function query(string $searchTerms, Builder $query): Builder
    {
        $toLowerSearch = strtolower($searchTerms);
        $query->where(function ($query) use ($toLowerSearch) {
            $query->orwhere('location', 'ILIKE', '%'.$toLowerSearch.'%');
            $query->orwhere('sponsor_name', 'ILIKE', '%'.$toLowerSearch.'%');
        });

        return $query;
    }
}
