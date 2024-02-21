<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Deals;

use App\Services\QueryServices\AbstractQueryService;
use App\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class SavedDeals
 */
class SavedDeals extends AbstractQueryService
{
    /**
     * Returns the Deal IDs that are saved by the Lender
     *
     * @param  array  $args
     * @return Collection
     */
    public function run(array $args): Collection
    {
        return $this->query($args['lenderId'])->get()->pluck('deal_id');
    }

    /**
     * Returns raw query for the Deal IDs that are saved by the Lender
     *
     * @param  int  $lenderId
     * @return Builder
     */
    public function query(int $lenderId): Builder
    {
        return DB::table('user_deal')
            ->select('deal_id')
            ->where('relation_type', '=', User::LENDER_SAVE_DEAL)
            ->where('user_id', '=', $lenderId)
            ->distinct();
    }
}
