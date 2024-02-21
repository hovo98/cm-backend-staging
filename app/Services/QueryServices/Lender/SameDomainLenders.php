<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender;

use App\Services\QueryServices\AbstractQueryService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class SameDomainLenders
 *
 * @author  Boris Djemrovski <boris@forwardslashny.com>
 */
class SameDomainLenders extends AbstractQueryService
{
    /**
     * Returns Lenders IDs with the same domain, current Lender excluded
     *
     * @param  array  $args
     * @return Collection
     */
    public function run(array $args): Collection
    {
        return $this->query($args['id'], $args['domain'])->get()->pluck('id');
    }

    /**
     * Returns raw query for Lenders IDs with the same domain, current Lender excluded
     *
     * @param  int  $id     Lender's ID
     * @param  string  $domain Lender's domain
     * @return Builder
     */
    public function query(int $id, string $domain): Builder
    {
        return DB::table('users')
                 ->select('id')
                 ->where('role', '=', 'lender')
                 ->where('email', 'LIKE', "%@${domain}")
                 ->whereNotNull(DB::raw("metas::jsonb->'perfect_fit'"))
                 ->where('id', '!=', $id)
                 ->whereNull('deleted_at');
    }
}
