<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Broker\Quotes;

use App\Services\QueryServices\AbstractQueryService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Class FilterBySearchLocation
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class FilterBySearchLocation extends AbstractQueryService
{
    /**
     * Returns the Deal IDs that are matched with search term by location
     *
     * @param  array  $args
     * @return Collection
     */
    public function run(array $args): Collection
    {
        return $this->query($args['searchLocation'], $args['query'])->get()->pluck('id');
    }

    /**
     * Returns raw query for the Deal IDs that are matched with search term by location
     *
     * @param  string  $searchLocation
     * @param  Builder  $query
     * @return Builder
     */
    public function query(string $searchLocation, Builder $query): Builder
    {
        $query->where('location', 'LIKE', '%'.(strtolower($searchLocation.'%')));

        return $query;
    }
}
