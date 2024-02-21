<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Deals;

use App\Lender;
use App\Services\QueryServices\AbstractQueryService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class FilterByLocation
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class FilterByLocation extends AbstractQueryService
{
    /**
     * Returns the Deal IDs that are matched with Lender working areas and removed excluded area
     *
     * @param  array  $args
     * @return Collection
     */
    public function run(array $args): Collection
    {
        return $this->query($args['locations'], $args['query'])->get()->pluck('id');
    }

    /**
     * Returns raw query for the Deal IDs that are matched with Lender working areas and removed excluded area
     *
     * @param  array  $locations
     * @param  Builder  $query
     * @return Builder
     */
    public function query(array $locations, Builder $query): Builder
    {
        $query = DB::table('deals')
                    ->whereNull('deleted_at')
                    ->where('finished', '=', true)
                    ->select('deals.id');
        $count = 1;
        $query->where(function ($query) use ($locations, $count) {
            foreach ($locations as $location) {
                if ($location['long_name'] === 'United States') {
                    $query->where('data->location->country', $location['long_name']);

                    continue;
                }

                $clause = 'orWhere';
                if ($count === 1) {
                    $clause = 'where';
                }
                $checkCounty = true;
                if ($location['state'] && $location['city']) {
                    $checkCounty = false;
                }
                $query->$clause(function ($query) use ($location, $checkCounty) {
                    if ($location['state']) {
                        $query->where('deals.data->location->state', $location['state']);
                    }
                    if ($location['county'] && $checkCounty) {
                        $query->where('deals.data->location->county', $location['county']);
                    }
                    if ($location['city']) {
                        //$query->where('deals.data->location->city', $location['city']);
                        $query->where(function ($q) use ($location) {
                            $q->orwhereJsonContains('deals.data->location->city', '');
                            $q->orwhere('deals.data->location->city', $location['city']);
                        });
                    }
                    if ($location['sublocality']) {
                        //$query->where('deals.data->location->sublocality', $location['sublocality']);
                        $query->where(function ($q) use ($location) {
                            $q->orwhereJsonContains('deals.data->location->sublocality', '');
                            $q->orwhere('deals.data->location->sublocality', $location['sublocality']);
                        });
                    }
                    if ($location['zip_code']) {
                        $query->where('deals.data->location->zip_code', $location['zip_code']);
                    }
                });
                $count++;
            }
        });

        return $query;
    }
}
