<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Deals;

use App\Deal;
use App\Services\QueryServices\AbstractQueryService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Class FilterByAssetType
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class FilterByAssetType extends AbstractQueryService
{
    public function run(array $args): Collection
    {
        return $this->query($args['assetType'], $args['min_amount'], $args['max_amount'], $args['query'])->get()->pluck('id');
    }

    /**
     * @param  array  $assetType
     * @param $min_amount
     * @param $max_amount
     * @param  Builder  $query
     * @return Builder
     */
    public function query(array $assetType, $min_amount, $max_amount, Builder $query): Builder
    {
        $isMixedUse = in_array(Deal::MIXED_USE, $assetType);
        $isMultifamily = in_array(Deal::MULTIFAMILY, $assetType);
        $isConstruction = in_array(Deal::CONSTRUCTION, $assetType);
        $multiMin = $min_amount ?? 0;
        $multiMax = $max_amount ?? 0;

        // If is multifamily remove from asset types because of amount
        if ($isMultifamily && $multiMin && $multiMax) {
            $key = array_search(Deal::MULTIFAMILY, $assetType);
            unset($assetType[$key]);
        }

        $query->where(function ($q) use ($assetType, $isMixedUse, $isConstruction, $isMultifamily, $multiMin, $multiMax) {
            $q->whereIn('deal_asset_type.asset_type_id', $assetType);

            if (! $isMixedUse && ! $isConstruction) {
                $q->where('deals.data->inducted->property_type->mixed', false);
            }
            if ($isConstruction) {
                $q->where(function ($buildQuery) {
                    $buildQuery->orwhere('deals.data->inducted->property_type->mixed', false);
                    $buildQuery->orwhere('deals.data->inducted->property_type->mixed', true);
                });
            }
            if ($isMultifamily && $multiMin && $multiMax) {
                $q->orwhere(function ($buildQuery) use ($multiMin, $multiMax) {
                    $buildQuery->whereRaw("(deals.data->'inducted'->>'multifamilyAmount')::bigint between $multiMin and $multiMax");
                    $buildQuery->where('deals.main_type', 8);
                });
            }
        });

        return $query;
    }
}
