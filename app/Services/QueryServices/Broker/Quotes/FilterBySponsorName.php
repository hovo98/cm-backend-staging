<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Broker\Quotes;

use App\Services\QueryServices\AbstractQueryService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Class FilterBySponsorName
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class FilterBySponsorName extends AbstractQueryService
{
    /**
     * Returns the Deal IDs that are matched with filtering by sponsor name
     *
     * @param  array  $args
     * @return Collection
     */
    public function run(array $args): Collection
    {
        return $this->query($args['sponsorNames'], $args['sponsorName'], $args['query'])->get()->pluck('id');
    }

    /**
     * Returns raw query for the Deal IDs that are matched with search filtering by sponsor name
     *
     * @param  array  $sponsorNames
     * @param  string  $sponsorName
     * @param  Builder  $query
     * @return Builder
     */
    public function query(array $sponsorNames, string $sponsorName, Builder $query): Builder
    {
        if (! empty($sponsorNames)) {
            $toLowerCaseSponsors = array_map(function ($name) {
                return ucfirst(strtolower($name));
            }, $sponsorNames);
            $query->whereIn('sponsor_name', $toLowerCaseSponsors);
        } elseif ($sponsorName) {
            $query->where('sponsor_name', 'ILIKE', '%'.strtolower($sponsorName).'%');
        }

        return $query;
    }
}
