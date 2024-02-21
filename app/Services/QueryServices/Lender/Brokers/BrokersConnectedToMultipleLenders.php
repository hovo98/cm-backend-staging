<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Brokers;

use App\Services\QueryServices\AbstractQueryService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class BrokersConnectedToMultipleLenders
 *
 * @author  Boris Djemrovski <boris@forwardslashny.com>
 */
class BrokersConnectedToMultipleLenders extends AbstractQueryService
{
    /**
     * Returns the IDs of the Brokers which are connected to multiple Lenders
     *
     * @param  Builder|array  $args ['in'] Raw query or list of lenders' IDs
     * @return Collection
     */
    public function run(array $args): Collection
    {
        return $this->query($args['in'])->get()->pluck('id');
    }

    /**
     * Returns raw query for the IDs of the Brokers which are connected to the Lenders
     *
     * @param  Builder|array  $in Raw query or list of lenders' IDs
     * @return Builder
     */
    public function query($in): Builder
    {
        return DB::table('users')
                 ->select('users.id')
                 ->join('broker_lender', 'users.id', '=', 'broker_lender.broker_id')
                 ->whereIn('broker_lender.lender_id', $in)
                 ->whereNull('users.deleted_at')
                 ->distinct();
    }
}
