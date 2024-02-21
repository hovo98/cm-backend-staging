<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Deals;

use App\Services\QueryServices\AbstractQueryService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class GetLendersForDeal
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class GetLendersForDeal extends AbstractQueryService
{
    /**
     * Returns the Lenders that has perfect fit for this deal
     *
     * @param  array  $args
     * @return Collection
     */
    public function run(array $args): Collection
    {
        return $this->query($args['dealDollarAmount'], $args['dealLocation'], $args['dealAssetTypes'])->get();
    }

    /**
     * Returns raw query for the Deal IDs that are ignored or archived by the Lender
     *
     * @param  int  $dealDollarAmount
     * @param  array  $dealLocations
     * @param  array  $dealAssetTypes
     * @return Builder
     */
    public function query(int $dealDollarAmount, array $dealLocations, array $dealAssetTypes): Builder
    {
        return DB::table('users')
            ->where('role', 'lender')
            ->whereNull('deleted_at')
            ->whereNotNull(DB::raw("metas::jsonb->'perfect_fit'"))
            ->whereRaw("(users.metas->'perfect_fit'->'loan_size'->>'min')::bigint <= $dealDollarAmount")
            ->whereRaw("(users.metas->'perfect_fit'->'loan_size'->>'max')::bigint >= $dealDollarAmount")
            ->where(function ($q) use ($dealAssetTypes) {
                foreach ($dealAssetTypes as $dealAssetType) {
                    $q->orWhereJsonContains('users.metas->perfect_fit->asset_types', $dealAssetType);
                }
            })
            ->crossJoin(DB::raw("lateral jsonb_to_recordset(users.metas->'perfect_fit'->'areas') as items(area text)"))
            ->where(function ($q) use ($dealLocations) {
                foreach ($dealLocations as $dealLocation) {
                    $q->orWhere('items.area', 'ILIKE', '%'.$dealLocation.'%');
                }
            });
    }
}
