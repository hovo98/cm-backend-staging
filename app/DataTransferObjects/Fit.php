<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

/**
 * Class Fit
 *
 * Contains uniformed data of Perfect or Close Fit for Lender's Deal Preferences
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
class Fit
{
    /**
     * @var FitArea[]
     */
    public $areas;

    /**
     * @var LoanSize
     */
    public $loan_size;

    /**
     * @var int[]
     */
    public $asset_types;

    /**
     * @var Multifamily
     */
    public $multifamily;

    /**
     * @var int[]
     */
    public $other_asset_types;

    /**
     * @var int[]
     */
    public $type_of_loans;

    /**
     * @var string[]
     */
    private $asset_types_dict = [
        1 => 'Retail',
        2 => 'Office',
        3 => 'Industrial',
        4 => 'Mixed Use',
        5 => 'Construction',
        6 => 'Owner Occupied',
        7 => 'Land',
        8 => 'Multifamily',
    ];

    /**
     * @var string[]
     */
    private $other_asset_types_dict = [
        1 => 'Healthcare',
        2 => 'Hospitality',
        3 => 'Agriculture',
        4 => 'Non-profits',
        5 => 'Bifurcated Assets',
        6 => 'Ground lease',
        7 => 'Fee deals',
    ];

    /**
     * @var string[]
     */
    private $type_of_loans_dict = [
        1 => 'Hard Money/Bridge',
        2 => 'Agency',
        3 => 'CMBS',
        4 => 'Balance Sheet',
    ];

    /**
     * Fit constructor.
     *
     * @param  array  $areas
     * @param  array|LoanSize  $loanSize
     * @param  int[]  $assetTypes
     * @param    $multifamily
     * @param  int[]  $otherAssetTypes
     * @param  int[]  $typeOfLoans
     */
    public function __construct(array $areas, $loanSize, array $assetTypes, $multifamily = null, array $otherAssetTypes = [], array $typeOfLoans = [])
    {
        $this->setAreas($areas);
        $this->setLoanSize($loanSize);
        $this->asset_types = $assetTypes;
        $this->setMultifamily($multifamily);
        $this->other_asset_types = $otherAssetTypes;
        $this->type_of_loans = $typeOfLoans;
    }

    /**
     * @return FitArea[]
     */
    public function getAreas(): array
    {
        return $this->areas;
    }

    /**
     * @return LoanSize
     */
    public function getLoanSize(): LoanSize
    {
        return $this->loan_size;
    }

    /**
     * @return int[]
     */
    public function getAssetTypes(): array
    {
        return $this->asset_types;
    }

    /**
     * @return int[]
     */
    public function getTypeOfLoansLender(): array
    {
        return $this->type_of_loans ?? [];
    }

    /**
     * @return string[]
     */
    public function getAssetTypesNames(): array
    {
        $assetTypes = [];

        foreach ($this->asset_types as $assetType) {
            $assetTypes[$assetType] = $this->asset_types_dict[$assetType];
        }

        return $assetTypes;
    }

    /**
     * @return string[]
     */
    public function getTypeOfLoansNamesLender(): array
    {
        $typeOfLoans = [];

        foreach ($this->type_of_loans as $typeOfLoan) {
            $typeOfLoans[$typeOfLoan] = $this->type_of_loans_dict[$typeOfLoan];
        }

        return $typeOfLoans;
    }

    /**
     * @return null|Multifamily
     */
    public function getMultifamily(): ?Multifamily
    {
        return $this->multifamily;
    }

    /**
     * @param  array  $areas
     * @return
     */
    private function setAreas(array $areas): void
    {
        $this->areas = array_map([$this, 'mapArea'], $areas);
    }

    /**
     * @param $area
     * @return FitArea
     */
    private function mapArea($area): FitArea
    {
        if ($area instanceof FitArea) {
            return $area;
        }

        return new FitArea($area);
    }

    /**
     * @param  array|LoanSize  $loanSize
     */
    private function setLoanSize($loanSize): void
    {
        if ($loanSize instanceof LoanSize) {
            $this->loan_size = $loanSize;
        } else {
            $this->loan_size = new LoanSize($loanSize['min'], $loanSize['max']);
        }
    }

    /**
     * @param  array|Multifamily  $multifamily
     */
    private function setMultifamily($multifamily): void
    {
        if (empty($multifamily['min_amount']) || empty($multifamily['max_amount'])) {
            return;
        }

        if ($multifamily instanceof Multifamily) {
            $this->multifamily = $multifamily;

            return;
        }

        $this->multifamily = new Multifamily($multifamily['min_amount'], $multifamily['max_amount']);
    }

    public function getWorkingAreas(): array
    {
        $workingAreas = [];

        foreach ($this->areas as $area) {
            $workingAreas[] = $area->workingArea();
        }

        return $workingAreas;
    }

    public function getExcludedAreas(): array
    {
        $excludedAreas = [];

        foreach ($this->areas as $area) {
            $excludedAreas[] = $area->excludedArea();
        }

        return array_merge(...$excludedAreas);
    }

    /**
     * @return int[]
     */
    public function getOtherAssetTypes(): array
    {
        return $this->other_asset_types ?? [];
    }

    /**
     * @return string[]
     */
    public function getOtherAssetTypesNames(): array
    {
        $otherAssetTypes = [];

        foreach ($this->other_asset_types as $otherAssetType) {
            $otherAssetTypes[$otherAssetType] = $this->other_asset_types_dict[$otherAssetType];
        }

        return $otherAssetTypes;
    }
}
