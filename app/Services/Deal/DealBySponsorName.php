<?php

namespace App\Services\Deal;

use App\DataLog;
use Illuminate\Support\Facades\DB;

class DealBySponsorName
{
    /**
     * @param string $sponsorName
     * @return array
     */
    public static function getList($sponsorName = '')
    {
        DataLog::recordSimple('DealBySponsorName', 'filter-quotes');

        $query = "select d.id from deals d
              cross join lateral jsonb_to_recordset(d.data->'sponsor'->'sponsorInfo') as sponsorInfo(name text)
              WHERE sponsorInfo.name LIKE '%". $sponsorName ."%' AND d.deleted_at IS NULL";
        $sponsors = DB::select($query);

        return array_map(function ($el) {
            return $el->id;
        }, $sponsors);
    }
}
