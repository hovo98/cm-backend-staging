<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\MigrationAWSJobs;
use App\Lender;

/**
 * Class MigrationAWSS3
 */
class MigrationAWSS3 extends Controller
{
    /**
     * Change structure for other expenses field
     */
    public function updateFromLocalToAws()
    {
        Lender::chunk(10, function ($lenders) {
            foreach ($lenders as $lender) {
                MigrationAWSJobs::dispatch($lender);
            }
        });
    }
}
