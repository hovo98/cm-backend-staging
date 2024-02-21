<?php

namespace App\Jobs;

use App\Lender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use League\Csv\Writer;

class FormatLenderPreferences implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public string $path;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($path, public Collection $lenders)
    {
        $this->path = $path;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $lenders = $this->lenders->map(function ($lender) {
            /** @var Lender $lender */
            $fit = $lender->getPerfectFit();

            if (is_null($fit)) {
                return [];
            }

            $company = $lender->getCompanyExport();

            $areas = $fit->getAreas();

            $inclusions = [];
            $exclusions = [];

            foreach ($areas as $area) {
                $inclusions[] = $area->formattedArea();
                $exclusions = array_merge($exclusions, $area->formattedExclusions());
            }

            $locations = implode('; ', $inclusions);
            $exclusions = implode('; ', $exclusions);

            $loanSize = $fit->getLoanSize();

            return array_merge([
                $lender->first_name,
                $lender->last_name,
                $lender->email,
                $company->company_name ?? '',
                $locations,
                $exclusions,
                $loanSize->min(),
                $loanSize->max(),
            ], $this->getAllAssets($fit));
        })
            ->reject(fn ($lenders) => empty($lenders));

        (Writer::createFromPath($this->path, 'a+'))->insertAll($lenders);
    }

    private function getAllAssets($fit): array
    {
        $asset_diff = [
            1 => 'Retail',
            2 => 'Office',
            3 => 'Industrial',
            4 => 'Mixed Use',
            5 => 'Construction',
            6 => 'Owner Occupied',
            7 => 'Land',
            8 => 'Multifamily',
        ];

        $other_asset_types_diff = [
            1 => 'Healthcare',
            2 => 'Hospitality',
            3 => 'Agriculture',
            4 => 'Non-profits',
            5 => 'Bifurcated Assets',
            6 => 'Ground lease',
            7 => 'Fee deals',
        ];

        $type_of_loans_diff = [
            1 => 'Hard Money/Bridge',
            2 => 'Agency',
            3 => 'CMBS',
            4 => 'Balance Sheet',
        ];

        $assetTypes = $this->getSortedAssets($fit->getAssetTypesNames() ?? [], $asset_diff);
        $otherAssetTypes = $this->getSortedAssets($fit->getOtherAssetTypesNames() ?? [], $other_asset_types_diff);
        $typeOfLoans = $this->getSortedAssets($fit->getTypeOfLoansNamesLender() ?? [], $type_of_loans_diff);
        if (! empty($otherAssetTypes)) {
            $assetTypes = array_merge($assetTypes, $otherAssetTypes, $typeOfLoans);
        }

        return $assetTypes;
    }

    /**
     * @param $assetTypes
     * @param $asset_diff
     * @return array
     */
    private function getSortedAssets($assetTypes, $asset_diff): array
    {
        $missing = array_diff_assoc($asset_diff, $assetTypes);
        foreach ($missing as $key => $missed) {
            $assetTypes[$key] = '';
        }
        ksort($assetTypes, SORT_NUMERIC);

        return $assetTypes;
    }
}
