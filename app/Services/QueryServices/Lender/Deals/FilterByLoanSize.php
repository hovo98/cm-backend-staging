<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Deals;

use App\Services\QueryServices\AbstractQueryService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Class FilterByLoanSize
 *
 * @author  Boris Djemrovski <boris@forwardslashny.com>
 */
class FilterByLoanSize extends AbstractQueryService
{
    /**
     * Returns the Deal IDs that are ignored or archived by the Lender
     *
     * @param  array  $args {min: int, max: int}
     * @return Collection
     */
    public function run(array $args): Collection
    {
        return $this->query($args['min'], $args['max'], $args['query'])->get();
    }

    /**
     * Returns raw query for the Deal IDs that are ignored or archived by the Lender
     *
     * @param  int  $min
     * @param  int  $max
     * @param  Builder  $query
     * @return Builder
     */
    public function query(int $min, int $max, Builder $query): Builder
    {
        $query->whereBetween('dollar_amount', [$min, $max]);

        return $query;
    }
}
