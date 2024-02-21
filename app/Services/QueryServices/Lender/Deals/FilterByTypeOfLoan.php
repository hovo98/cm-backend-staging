<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Deals;

use App\Services\QueryServices\AbstractQueryService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class FilterByTypeOfLoan
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class FilterByTypeOfLoan extends AbstractQueryService
{
    /**
     * Returns the Deal IDs that are has the same type of loan as the Lender
     *
     * @param  array  $args {min: int, max: int}
     * @return Collection
     */
    public function run(array $args): Collection
    {
        return $this->query($args['type_of_loans'], $args['query'])->get()->pluck('id');
    }

    /**
     * Returns raw query for the Deal IDs that are has the same type of loan as the Lender
     *
     * @param  array  $typeOfLoans
     * @param  Builder  $query
     * @return Builder
     */
    public function query(array $typeOfLoans, Builder $query): Builder
    {
        if (empty($typeOfLoans)) {
            return $query;
        }

        $getDealsWithFiltredTypeLoans = DB::table('deal_type_of_loan')
            ->whereIn('type_of_loan', $typeOfLoans)
            ->select('deal_id')
            ->get()
            ->pluck('deal_id')
            ->toArray();
        $dealsIdsWithFiltredTypeLoans = array_unique($getDealsWithFiltredTypeLoans, SORT_REGULAR);

        $checkFilteredDealsId = DB::table('deals')
            ->whereNull('deleted_at')
            ->where('finished', '=', true)
            ->whereIn('id', $dealsIdsWithFiltredTypeLoans)
            ->select('id')
            ->get()
            ->pluck('id')
            ->toArray();

        $query->whereIn('deals.id', $checkFilteredDealsId);

        return $query;
    }
}
