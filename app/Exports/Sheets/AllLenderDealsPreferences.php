<?php

declare(strict_types=1);

namespace App\Exports\Sheets;

use App\Lender;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class AllLenderDealsPreferences
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class AllLenderDealsPreferences extends SheetAbstract implements SheetInterface
{
    /**
     * @return Builder
     */
    public function query(): Builder
    {
        return Lender::query()->where('role', 'lender')->whereNotNull('metas');
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Lenders Deal Preferences';
    }

    /**
     * @var Lender
     *
     * @return array
     */
    public function map(Model $obj): array
    {
        $fit = $obj->getPerfectFit();

        if (is_null($fit)) {
            return [];
        }

        $company = $obj->getCompanyExport();

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

        $mapped = collect([
            $obj->first_name,
            $obj->last_name,
            $obj->email,
            $company->company_name ?? '',
            $locations,
            $exclusions,
            $loanSize->min(),
            $loanSize->max(),
        ])->merge($this->getSortedAllAssetTypes($fit));

        return $mapped
            ->map([$this, 'mapNullToString'])
            ->toArray();
    }

    /**
     * @return Collection
     */
    public function headings(): Collection
    {
        return collect([
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
        ]);
    }

    /**
     * @param $fit
     * @return array
     */
    private function getSortedAllAssetTypes($fit): array
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

        $assetTypes = $this->getSortedAssetTypes($fit->getAssetTypesNames() ?? [], $asset_diff);
        $otherAssetTypes = $this->getSortedAssetTypes($fit->getOtherAssetTypesNames() ?? [], $other_asset_types_diff);
        $typeOfLoans = $this->getSortedAssetTypes($fit->getTypeOfLoansNamesLender() ?? [], $type_of_loans_diff);
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
    private function getSortedAssetTypes($assetTypes, $asset_diff): array
    {
        $missing = array_diff_assoc($asset_diff, $assetTypes);
        foreach ($missing as $key => $missed) {
            $assetTypes[$key] = '';
        }
        ksort($assetTypes, SORT_NUMERIC);

        return $assetTypes;
    }
}
