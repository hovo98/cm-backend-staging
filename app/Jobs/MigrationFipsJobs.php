<?php

declare(strict_types=1);

namespace App\Jobs;

use App\DataTransferObjects\Fit;
use App\DataTransferObjects\FitArea;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Class DealCreated
 *
 * @author Andrej Smit <andrej.smit@forwardslashny.com>
 */
class MigrationFipsJobs implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $lender;

    public function __construct($lender)
    {
        $this->lender = $lender;
    }

    public function handle()
    {
        $fit = $this->lender->getPerfectFit() ?? $this->lender->getCloseFit();
        $areas = new Collection(
            $fit instanceof Fit
                ? $fit->getAreas()
                : []
        );
        if ($fit instanceof Fit) {
            $areas = $this->fipsIntoAreas($areas, $this->lender->id);
            $this->lender->updateFit('perfect', $fit);
        }
    }

    private function fipsIntoAreas(Collection $areas, int $lenderId)
    {
        $areas->each(function (FitArea $area) {
            $area
                ->fipsArea(
                    function (float $latitude, float $longitude, string $formattedAddress) {
                        if ($formattedAddress === 'United States') {
                            return '';
                        }

                        return '0';
                    }
                )
                ->fipsExclusions(
                    function (float $latitude, float $longitude, string $formattedAddress) {
                        if ($formattedAddress === 'United States') {
                            return '';
                        }

                        return '0';
                    }
                );
        });

        return $areas;
    }

    private function updateFipsCode(float $latitude, float $longitude)
    {
        try {
            $response = Http::get('https://geo.fcc.gov/api/census/block/find?format=json&latitude='.$latitude.'&longitude='.$longitude);
        } catch (\Throwable $exception) {
            Log::info('error api');
            Log::info($exception->getMessage());
        }

        return $response['Block']['FIPS'];
    }
}
