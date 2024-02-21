<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Deals;

use App\Services\QueryServices\AbstractQueryService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Class FilterLocationPerfectCloseFit
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class FilterLocationPerfectCloseFit extends AbstractQueryService
{
    /**
     * @var FilterByLocation
     */
    private $filterByLocation;

    /**
     * @var GetPerfectCloseFitExcludedAreas
     */
    private $getPerfectCloseFitExcludedAreas;

    /**
     * @param  FilterByLocation  $filterByLocation
     * @param  GetPerfectCloseFitExcludedAreas  $getPerfectCloseFitExcludedAreas
     */
    public function __construct(FilterByLocation $filterByLocation, GetPerfectCloseFitExcludedAreas $getPerfectCloseFitExcludedAreas)
    {
        $this->getPerfectCloseFitExcludedAreas = $getPerfectCloseFitExcludedAreas;
        $this->filterByLocation = $filterByLocation;
    }

    /**
     * Returns the Deal IDs that are matched with Lender working areas and removed excluded area
     *
     * @param  array  $args
     * @return Collection
     */
    public function run(array $args): Collection
    {
        return $this->query($args['workingAreas'], $args['excludedArea'], $args['query'])->get()->pluck('id');
    }

    /**
     * Returns raw query for the Deal IDs that are matched with Lender working areas and removed excluded area
     *
     * @param  array  $workingAreas
     * @param  array  $excludedArea
     * @param  Builder  $query
     * @return Builder
     */
    public function query(array $workingAreas, array $excludedArea, Builder $query): Builder
    {
        $perfectLocation = $this->filterByLocation->query($workingAreas, $query);
        $perfectLocationIds = $perfectLocation->get()->pluck('id')->toArray();
        $excludedIds = [];
        if (! empty($excludedArea)) {
            $excluded = $this->getPerfectCloseFitExcludedAreas->query($excludedArea, $perfectLocationIds);
            $excludedIds = $excluded->get()->pluck('id')->toArray();
        }

        if (! empty($excludedIds)) {
            $perfectLocationIds = array_diff($perfectLocationIds, $excludedIds);
        }
        $query->whereIn('deals.id', $perfectLocationIds);

        return $query;
    }
}
