<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\MigrationFipsJobs;
use App\Lender;

/**
 * Class MigrationFipsCode
 */
class MigrationFipsCode extends Controller
{
    /**
     * Change structure for other expenses field
     */
    public function updateFips()
    {
        Lender::where('role', '=', 'lender')->withTrashed()->chunk(10, function ($lenders) {
            foreach ($lenders as $lender) {
                MigrationFipsJobs::dispatch($lender);
            }
        });
    }
}
