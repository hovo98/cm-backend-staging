<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Deals;

use App\Services\QueryServices\AbstractQueryService;
use App\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class IgnoredDealsMultipleLenders
 *
 * @author  Boris Djemrovski <boris@forwardslashny.com>
 */
class IgnoredDealsMultipleLenders extends AbstractQueryService
{
    /**
     * Returns the Deal IDs which are ignored by a list of Lenders
     *
     * @param  array  $args Raw query or list of lenders' IDs
     * @return Collection
     */
    public function run(array $args): Collection
    {
        return $this->query($args['in'])->get()->pluck('deal_id');
    }

    /**
     * Returns Query for the Deal IDs which are ignored by a list of Lenders
     *
     * @param  Builder|array  $in Raw query or list of lenders' IDs
     * @return Builder
     */
    public function query($in): Builder
    {
        return DB::table('user_deal')
                 ->select(['deal_id'])
                 ->where('relation_type', '=', User::LENDER_IGNORE_DEAL)
                 ->whereIn('user_id', $in)
                 ->distinct();
    }
}
