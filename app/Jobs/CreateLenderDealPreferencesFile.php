<?php

namespace App\Jobs;

use App\Lender;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;

class CreateLenderDealPreferencesFile implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public User $user)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $jobs = [];
        $filename = 'Lenders Deal Preferences.csv';

        $path = Storage::disk('tmp')->path($filename);
        $csv = Writer::createFromPath($path, 'w+');
        $headers = [
            'First Name',
            'Last Name',
            'Email',
            'Bank Name',
            'Locations',
            'Exclusions',
            'Dollar Amount MIN',
            'Dollar Amount MAX',
            'Retail',
            'Office',
            'Industrial',
            'Mixed Use',
            'Construction',
            'Owner Occupied',
            'Land',
            'Multifamily',
            'Healthcare',
            'Hospitality',
            'Agriculture',
            'Non-profits',
            'Bifurcated Assets',
            'Ground lease',
            'Fee deals',
            'Hard Money/Bridge',
            'Agency',
            'CMBS',
            'Balance Sheet',
        ];

        $csv->insertOne($headers);


        Lender::query()
            ->orderBy('created_at', 'ASC')
            ->chunkById(500, function ($lenders) use (&$jobs, $path) {
                $jobs[] = new FormatLenderPreferences($path, $lenders);
            });


        $jobs = array_merge($jobs, [
            new UploadLendersExportToS3($filename),
            new MailDealPreferencesFile($filename, $this->user),
        ]);

        Bus::chain($jobs)->dispatch();
    }
}
