<?php

namespace App\Services\Deal;

use Illuminate\Support\Facades\DB;

/**
 * Class DealSponsors
 * @package App\Services\Deal
 */
class DealSponsorsByName
{
    /**
     * @param string $sponsorName
     * @return array
     */
    public static function getList($sponsorName = '')
    {
        $query = "select sponsorInfo.name from deals d
              cross join lateral jsonb_to_recordset(d.data->'sponsor'->'sponsorInfo') as sponsorInfo(name text)
              WHERE sponsorInfo.name LIKE '%". $sponsorName ."%' AND d.deleted_at IS NULL GROUP BY sponsorInfo.name";
        $sponsors = DB::select($query);

        return array_map(function ($el) {
            return ['name' => $el->name];
        }, $sponsors);
    }
}
