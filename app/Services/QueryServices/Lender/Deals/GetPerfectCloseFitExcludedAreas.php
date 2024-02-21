<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Deals;

use App\Lender;
use App\Services\QueryServices\AbstractQueryService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class GetPerfectCloseFitExcludedAreas
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class GetPerfectCloseFitExcludedAreas extends AbstractQueryService
{
    /**
     * Returns the excluded location strings that Lender is not working
     *
     * @param  array  $args
     * @return Collection
     */
    public function run(array $args): Collection
    {
        return $this->query($args['excludedArea'], $args['perfectLocationIds'])->get()->pluck('id');
    }

    /**
     * @param  array  $excludedArea
     * @param $perfectLocationIds
     * @return Builder
     */
    public function query(array $excludedArea, $perfectLocationIds): Builder
    {
        $count = 1;

        $query = DB::table('deals')
            ->select('deals.id')
            ->whereNull('deleted_at')
            ->where('finished', '=', true)
            ->whereIn('deals.id', $perfectLocationIds)
            ->where(function ($query) use ($excludedArea, $count) {
                foreach ($excludedArea as $location) {
                    $clause = $count === 1 ? 'where' : 'orWhere';

                    $checkCounty = ($location['state'] && $location['city']) ? false : true;

                    $query->$clause(function ($query) use ($location, $checkCounty) {
                        $query->when($location['state'], function ($query) use ($location) {
                            $query->where('deals.data->location->state', $location['state']);
                        })
                        ->when($location['county'] && $checkCounty, function ($query) use ($location) {
                            $query->where('deals.data->location->county', $location['county']);
                        })
                        ->when($location['city'], function ($query) use ($location) {
                            $query->where('deals.data->location->city', $location['city']);
                        })
                        ->when($location['sublocality'], function ($query) use ($location) {
                            $query->where('deals.data->location->sublocality', $location['sublocality']);
                        })
                        ->when($location['zip_code'], function ($query) use ($location) {
                            $query->where('deals.data->location->zip_code', $location['zip_code']);
                        });
                    });

                    $count++;
                }
            });

        return $query;
    }
}
