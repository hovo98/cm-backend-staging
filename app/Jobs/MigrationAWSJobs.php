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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Class DealCreated
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class MigrationAWSJobs implements ShouldQueue
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
        if ($areas) {
            $this->mapPolygonsIntoAreas($areas, $this->lender->id);
        }
    }

    private function mapPolygonsIntoAreas(Collection $areas, int $lenderId)
    {
        $areas->each(function (FitArea $area) use ($lenderId) {
            $area
                ->mapPolygon(
                    function () use ($lenderId, $area) {
                        return $this->getPolygon($lenderId, FitArea::AREA_TYPE_WORKING_DIRECTORY, $area->area['lat'], $area->area['long']);
                    }
                )
                ->mapExclusions(
                    function (float $latitude, float $longitude) use ($lenderId) {
                        return $this->getPolygon($lenderId, FitArea::AREA_TYPE_EXCLUDED_DIRECTORY, $latitude, $longitude);
                    }
                );
        });

        return $areas;
    }

    private function getPolygon(int $lenderId, string $areaType, float $latitude, float $longitude)
    {
        $filePathAndName = sprintf(
            '%s/%s/%s/lat_%s_long_%s.json',
            FitArea::POLYGON_STORAGE_DIRECTORY,
            $lenderId,
            $areaType,
            $latitude,
            $longitude
        );
        Log::info($filePathAndName);
        try {
            $file = Storage::disk('cm-server-storage')->get($filePathAndName);
            if ($file) {
                Storage::disk(config('app.app_file_upload'))->put($filePathAndName, $file, []);
            }
        } catch (\Throwable $th) {
            Log::info('start');
            Log::info($lenderId);
            Log::info($th);
            Log::info('stop');
        }
    }
}
