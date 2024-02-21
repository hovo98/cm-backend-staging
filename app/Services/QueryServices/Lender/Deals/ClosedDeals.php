<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Deals;

use App\Quote;
use App\Services\QueryServices\AbstractQueryService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class ClosedDeals
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class ClosedDeals extends AbstractQueryService
{
    /**
     *  Returns closed deals id from another lender
     *
     * @param  array  $args id => int
     * @return Collection
     */
    public function run(array $args): Collection
    {
        return $this->query($args['id'])->get()->pluck('id');
    }

    /**
     * Returns closed deals id from another lender
     *
     * @param  int  $id
     * @return Builder
     */
    public function query(int $id): Builder
    {
        $builder = DB::table('deals')
            ->select('deals.id')
            ->join('quotes', 'deals.id', '=', 'quotes.deal_id')
            ->whereNull('deals.deleted_at');

        $builder->where(function ($query) use ($id) {
            $query->orWhere('quotes.status', Quote::ACCEPTED)
            ->where('quotes.user_id', '!=', $id);
            $query->orWhere('quotes.status', Quote::SECOND_ACCEPTED)
                ->where('quotes.user_id', '!=', $id);
        });

        $whereNotIn = DB::table('quotes')
            ->select('deal_id')
            ->where(function ($query) {
                $query->orWhere('quotes.status', Quote::ACCEPTED);
                $query->orWhere('quotes.status', Quote::SECOND_ACCEPTED);
            })->where('quotes.user_id', '=', $id)->pluck('deal_id')->toArray();

        $builder->whereNotIn('deals.id', $whereNotIn);

        return $builder;
    }
}
