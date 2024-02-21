<?php

namespace App\DataTransferObjects;

use App\Deal;
use App\User;
use Carbon\Carbon;

class DealMapper extends JsonbMapper
{
    protected $type = Deal::class;

    public function mapFromEloquent($obj = null): array
    {
        $activeObj = $obj ? $obj : $this->obj;

        $finished_at = data_get($activeObj, 'data.finished_at', $activeObj->updated_at);

        return [
            'id' => $activeObj->id,
            'finished' => $activeObj->finished,
            'updated_at' => $activeObj->updated_at,
            'finished_at' => $this->dateFinishedDeal((string) $finished_at),
            'lastStepStatus' => $activeObj->lastStepStatus,
            'user_id' => $activeObj->user_id,
            'termsheet' => $activeObj->termsheet,
        ] + $activeObj->data;
    }

    public function mapFromEloquentWith($obj, $args): array
    {
        return $this->mapFromEloquent($obj) + $args;
    }

    public function mapFromQueryBuilder($obj = null): array
    {
        $activeObj = $obj ? $obj : $this->obj;

        $finished_at = data_get($activeObj, 'data.finished_at', $activeObj->updated_at);

        return [
            'id' => $activeObj->id,
            'finished' => $activeObj->finished,
            'updated_at' => $activeObj->updated_at,
            'finished_at' => $this->dateFinishedDeal((string) $finished_at),
            'lastStepStatus' => $activeObj->lastStepStatus,
            'user_id' => $activeObj->user_id,
            'termsheet' => $activeObj->termsheet,
        ] + json_decode($activeObj->data, true);
    }

    public function dataUnit()
    {
        return [
            'id' => $this->obj->id,
            'termsheet' => $this->obj->termsheet,
            'step' => 0,
            'lastStepStatus' => $this->stringUnit(),
            'finished' => $this->booleanUnit(),
            'updated_at' => $this->obj->updated_at,
            'finished_at' => $this->dateFinishedDeal($this->obj->id),
            'location' => $this->locationUnit(),
            'sponsor' => $this->sponsorUnit(),
            'upload_pfs' => $this->sponsorUnit(),
            'assets' => $this->arrayUnit(),
            'block_and_lot' => $this->blockAndLotUnit(),
            'loan_type' => $this->enumUnit(),
            'show_address_purchase' => $this->stringUnit(),
            'purchase_loan' => $this->purchaseLoanUnit(),
            'refinance_loan' => $this->refinanceLoanUnit(),
            'construction_loan' => $this->constructionLoanUnit(),
            'investment_details' => $this->investmentDetailsUnit(),
            'owner_occupied' => $this->ownerOccupiedUnit(),
            'construction' => $this->constructionUnit(),
            'property_type' => $this->enumUnit(),
            'rent_roll' => $this->rentRollUnit(),
            'expenses' => $this->expensesUnit(),
            'existing' => $this->existingUnit(),
            'sensitivity' => $this->sensitivityUnit(),
            'type_of_loans' => $this->arrayUnit(),
            'total_quotes' => $this->numberUnit(),
            'has_new_quotes' => $this->numberUnit(),
            'loan_amount' => $this->numberUnit(),
            'type' => $this->enumUnit(),
            'show_address' => $this->booleanUnit(),
            'is_saved' => $this->booleanUnit(),
            'quoted' => $this->booleanUnit(),
        ];
    }

    private function sensitivityUnit()
    {
        return [
            'timeToClose' => $this->numberUnit(),
            'recourse' => $this->numberUnit(),
            'leverage' => $this->numberUnit(),
            'interestRate' => $this->numberUnit(),
            'fees' => $this->numberUnit(),
            'interestOnlyPeriod' => $this->numberUnit(),
            'noPrepaymentPenalty' => $this->numberUnit()
        ];
    }

    private function existingUnit()
    {
        return [
            'propertyType' => $this->stringUnit(),
            'free' => $this->stringUnit(),
            'warehouse' => $this->stringUnit(),
            'lender' => $this->stringUnit(),
        ];
    }

    private function expensesUnit()
    {
        return [
            'taxNumber' => $this->stringUnit(),
            'tax' => $this->stringUnit(),
            'expDate' => $this->stringUnit(),
            'phaseStructure' => $this->stringUnit(),
            'payroll' => $this->stringUnit(),
            'insurance' => $this->stringUnit(),
            'repairs' => $this->stringUnit(),
            'payrollAmount' => $this->stringUnit(),
            'electricity' => $this->stringUnit(),
            'electricityAmount' => $this->stringUnit(),
            'gas' => $this->stringUnit(),
            'gasAmount' => $this->stringUnit(),
            'commonArea' => $this->stringUnit(),
            'commonAreaAmount' => $this->stringUnit(),
            'water' => $this->stringUnit(),
            'waterAmount' => $this->stringUnit(),
            'management' => $this->stringUnit(),
            'managementAmount' => $this->stringUnit(),
            'legal' => $this->stringUnit(),
            'triple' => $this->stringUnit(),
            'reimbursement' => $this->stringUnit(),
            'otherExpenses' => [$this->otherExpensesUnit()],
            'additionalNotes' => $this->stringUnit(),
            'elevatorMaintenanceAmount' => $this->stringUnit(),
            'elevatorMaintenance' => $this->stringUnit(),
            'ooSewerAmount' => $this->stringUnit(),
            'gasSeparatelyMetered' => $this->stringUnit(),
            'managementCompanyName' => $this->stringUnit(),
            'ooWaterAmount' => $this->stringUnit(),
            'waterSeparatelyMetered' => $this->stringUnit(),
            'electricitySeparatelyMetered' => $this->stringUnit(),
            'totalExpenses' => $this->stringUnit(),
            'netOperatingIncome' => $this->stringUnit(),
            'totalBusinessOperatingIncome' => $this->stringUnit(),
        ];
    }

    private function rentRollUnit()
    {
        return [
            'table' => [$this->rentRollTableUnit()],
            'annual_income' => $this->stringUnit(),
            'potential_income' => $this->stringUnit(),
            'increaseProjection' => $this->stringUnit(),
            'increasedNotes' => $this->stringUnit(),
            'betterNotes' => $this->stringUnit(),
            'capExBudget' => $this->stringUnit(),
            'tiBudget' => $this->stringUnit(),
            'lcBudget' => $this->stringUnit(),
            'timeFrame' => $this->stringUnit(),
            'plannedImprovements' => $this->stringUnit(),
            'enterCopy' => $this->stringUnit(),
            'other_income' => [$this->otherIncomeUnit()],
            'totalIncome' => $this->stringUnit(),
            'monthle_total' => $this->stringUnit(),
            'annual_total' => $this->stringUnit(),
            'vacancy' => $this->stringUnit(),
            'occupiedGroos' => $this->stringUnit(),
            'annual_sf_total' => $this->stringUnit(),
        ];
    }

    private function rentRollTableUnit()
    {
        return [
            'occupied' => $this->booleanUnit(),
            'unit_type' => $this->stringUnit(),
            'name' => $this->stringUnit(),
            'unit' => $this->stringUnit(),
            'bedroom' => $this->stringUnit(),
            'lease_start' => $this->stringUnit(),
            'lease_end' => $this->stringUnit(),
            'sf' => $this->stringUnit(),
            'monthle_rent' => $this->stringUnit(),
            'annual_rent' => $this->stringUnit(),
            'annual_rent_sf' => $this->stringUnit(),
        ];
    }

    private function locationUnit()
    {
        return [
            'street_address' => $this->stringUnit(),
            'street_address_2' => $this->stringUnit(),
            'city' => $this->stringUnit(),
            'state' => $this->stringUnit(),
            'country' => $this->stringUnit(),
            'zip_code' => $this->stringUnit(),
            'place_id' => $this->stringUnit(),
            'sublocality' => $this->stringUnit(),
            'lat' => $this->numberUnit(),
            'long' => $this->numberUnit(),
            'county' => $this->stringUnit(),
            'street' => $this->stringUnit(),
        ];
    }

    private function sponsorUnit()
    {
        return [
            'sponsorInfo' => $this->sponsorInfoUnit(),
            'multiple' => $this->stringUnit(),
        ];
    }

    public function sponsorInfoUnit()
    {
        return [
            [
                'name' => $this->stringUnit(),
                'ownership' => $this->stringUnit(),
                'years_experience' => $this->stringUnit(),
                'family_experience' => $this->stringUnit(),
                'annual_income' => $this->stringUnit(),
                'annual_expenses' => $this->stringUnit(),
                'liabilities' => $this->stringUnit(),
                'assets_real_estate' => $this->stringUnit(),
                'assets_companies' => $this->stringUnit(),
                'assets_other' => $this->stringUnit(),
                'assets_liquid' => $this->stringUnit(),
                'net_worth' => $this->stringUnit(),
                'net_income' => $this->stringUnit(),
                'total_assets' => $this->stringUnit(),
            ],
        ];
    }

    private function blockAndLotUnit()
    {
        return [
            'blockAndLot' => $this->stringUnit(),
            'block' => $this->stringUnit(),
            'lot' => $this->stringUnit(),
        ];
    }

    private function purchaseLoanUnit()
    {
        return [
            'price' => $this->numberUnit(),
            'estimated_value' => $this->numberUnit(),
            'estimated_cap_rate' => $this->stringUnit(),
            'days_to_close' => $this->numberUnit(),
            'loan_amount' => $this->numberUnit(),
            'ltc_purchase' => $this->stringUnit(),
        ];
    }

    private function refinanceLoanUnit()
    {
        return [
            'purchasePrice' => $this->numberUnit(),
            'date' => $this->stringUnit(),
            'currentValue' => $this->numberUnit(),
            'list' => $this->stringUnit(),
            'loanAmount' => $this->numberUnit(),
        ];
    }

    private function constructionLoanUnit()
    {
        return [
            'buying_land' => $this->stringUnit(),
            'debt_on_property' => $this->stringUnit(),
            'purchase_price' => $this->stringUnit(),
            'purchase_date' => $this->stringUnit(),
            'debt_amount' => $this->stringUnit(),
            'lender_name' => $this->stringUnit(),
            'loanAmount' => $this->numberUnit(),
            'show_address_construction' => $this->stringUnit(),
        ];
    }

    private function investmentDetailsUnit()
    {
        return [
            'mixedUse' => $this->arrayUnit(),
            'propType' => $this->enumUnit(),
            'retailAmount' => $this->numberUnit(),
            'retailSquare' => $this->numberUnit(),
            'retailNumberOfUnitsOccupied' => $this->numberUnit(),
            'retailSquareFootageOccupied' => $this->numberUnit(),
            'retailType' => $this->numberUnit(),
            'multiAmount' => $this->numberUnit(),
            'multiSquare' => $this->numberUnit(),
            'multiNumberOfUnitsOccupied' => $this->numberUnit(),
            'multiSquareFootageOccupied' => $this->numberUnit(),
            'officeAmount' => $this->numberUnit(),
            'officeSquare' => $this->numberUnit(),
            'officeNumberOfUnitsOccupied' => $this->numberUnit(),
            'officeSquareFootageOccupied' => $this->numberUnit(),
            'warehouseAmount' => $this->numberUnit(),
            'warehouseSquare' => $this->numberUnit(),
            'warehouseNumberOfUnitsOccupied' => $this->numberUnit(),
            'warehouseSquareFootageOccupied' => $this->numberUnit(),
            'numberUnit' => $this->numberUnit(),
            'numberUnitOccupied' => $this->numberUnit(),
            'squareFootage' => $this->numberUnit(),
            'squareFootageOccupied' => $this->numberUnit(),
            'proposedUse' => $this->stringUnit(),
            'noteToLender' => $this->stringUnit(),

            'amountOfUnits' => $this->stringUnit(),
            'rentableSellable' => $this->stringUnit(),
            'retailFloors' => $this->stringUnit(),
            'multiAmountOfUnits' => $this->stringUnit(),
            'multiRentableSellable' => $this->stringUnit(),
            'multiFloors' => $this->stringUnit(),
            'officeAmountOfunits' => $this->stringUnit(),
            'officeRentableSellable' => $this->stringUnit(),
            'officeFloors' => $this->stringUnit(),
            'industrialAmountOfUnits' => $this->stringUnit(),
            'industrialRentableSellable' => $this->stringUnit(),
            'industrialFloors' => $this->stringUnit(),
        ];
    }

    private function ownerOccupiedUnit()
    {
        return [
            'business_name' => $this->stringUnit(),
            'business_description' => $this->stringUnit(),
            'sales_amount' => $this->stringUnit(),
            'profit_amount' => $this->stringUnit(),
            'borrower_own' => $this->stringUnit(),
            'business_age' => $this->stringUnit(),
            'sales_amount_YTD' => $this->stringUnit(),
            'profit_amount_YTD' => $this->stringUnit(),
            'employees' => $this->stringUnit(),
        ];
    }

    private function constructionUnit()
    {
        return [
            'date' => $this->stringUnit(),
            'land_cost' => $this->numberUnit(),
            'current_value' => $this->numberUnit(),
            'hard_cost' => $this->numberUnit(),
            'soft_cost' => $this->numberUnit(),
            'loan_amount' => $this->numberUnit(),
            'contractor_name' => $this->stringUnit(),
            'amount_units' => $this->numberUnit(),
            'square_footage' => $this->numberUnit(),
            'floors' => $this->numberUnit(),
            'plans' => $this->stringUnit(),
            'second_projection' => $this->booleanUnit(),
            'projections' => $this->stringUnit(),
            'projections_sales' => $this->numberUnit(),
            'projections_per_units' => $this->numberUnit(),
            'projections_per_sf' => $this->numberUnit(),
            'rental_per' => $this->stringUnit(),
            'rental_amount' => $this->numberUnit(),
            'rental_projections_per_units' => $this->numberUnit(),
            'rental_projections_per_sf' => $this->numberUnit(),
            'projectionMixedUse' => $this->arrayUnit(),
        ];
    }

    private function otherExpensesUnit()
    {
        return [
            'type' => $this->stringUnit(),
            'amount' => $this->stringUnit(),
        ];
    }

    private function otherIncomeUnit()
    {
        return [
            'type' => $this->stringUnit(),
            'amount' => $this->stringUnit(),
        ];
    }

    private function resetLoanType()
    {
        return [
            'sponsor' => $this->sponsorUnit(),
            'upload_pfs' => $this->sponsorUnit(),
            'assets' => $this->arrayUnit(),
            'purchase_loan' => $this->purchaseLoanUnit(),
            'refinance_loan' => $this->refinanceLoanUnit(),
            'construction_loan' => $this->constructionLoanUnit(),
            'investment_details' => $this->investmentDetailsUnit(),
            'owner_occupied' => $this->ownerOccupiedUnit(),
            'construction' => $this->constructionUnit(),
            'property_type' => $this->enumUnit(),
            'rent_roll' => $this->rentRollUnit(),
            'expenses' => $this->expensesUnit(),
            'existing' => $this->existingUnit(),
            'sensitivity' => $this->sensitivityUnit(),
            'type_of_loans' => $this->arrayUnit(),
        ];
    }

    private function resetPropertyType()
    {
        return [
            'sponsor' => $this->sponsorUnit(),
            'upload_pfs' => $this->sponsorUnit(),
            'assets' => $this->arrayUnit(),
            'investment_details' => $this->investmentDetailsUnit(),
            'owner_occupied' => $this->ownerOccupiedUnit(),
            'construction' => $this->constructionUnit(),
            'rent_roll' => $this->rentRollUnit(),
            'expenses' => $this->expensesUnit(),
            'existing' => $this->existingUnit(),
            'sensitivity' => $this->sensitivityUnit(),
        ];
    }

    private function resetTypeDetails()
    {
        return [
            'sponsor' => $this->sponsorUnit(),
            'upload_pfs' => $this->sponsorUnit(),
            'assets' => $this->arrayUnit(),
            'investment_details' => $this->investmentDetailsUnit(),
            'construction' => $this->constructionUnit(),
            'rent_roll' => $this->rentRollUnit(),
            'expenses' => $this->expensesUnit(),
            'sensitivity' => $this->sensitivityUnit(),
            'type_of_loans' => $this->arrayUnit(),
        ];
    }

    private function resetTypeOfLoans()
    {
        return [
            'type_of_loans' => $this->arrayUnit(),
        ];
    }

    protected function compose_soft($args)
    {
        $data = $this->dataUnit();
        $existingData = $this->obj->data ?? [];
        foreach ($existingData as $key => $val) {
            $data[$key] = $val;
        }
        foreach ($args as $key => $val) {
            $data[$key] = $val;
        }
        $this->obj->data = $data;
    }

    public function resetData($typeData)
    {
        if ($typeData === 'loan_type') {
            $dataReset = $this->resetLoanType();
        }

        if ($typeData === 'property_type') {
            $dataReset = $this->resetPropertyType();
        }

        if ($typeData === 'investment_type') {
            $dataReset = $this->resetTypeDetails();
        }

        if ($typeData === 'loan_amount') {
            $dataReset = $this->resetTypeOfLoans();
        }

        $existingData = $this->obj->data ?? [];
        foreach ($existingData as $key => $val) {
            $data[$key] = $val;
        }
        foreach ($dataReset as $key => $val) {
            $data[$key] = $val;
        }

        $this->obj->data = $data;

        return $this->obj;
    }

    public function appendInducted($data)
    {
        $loanType = (int) $data['loan_type'];
        $loanAmount = 0;
        $propertyType = $loanType == 3 ? 3 : $data['property_type'];
        $assetTypes = [];
        $isMixed = false;
        $location = '';
        $sponsorNames = '';
        $sponsorName = '';
        $mainType = 0;
        $multifamilyAmount = 0;

        if ($data['location']) {
            $location = $data['location']['state'].' '.$data['location']['city'].' '.$data['location']['sublocality']
                .' '.$data['location']['street_address'].' '.$data['location']['zip_code'].' '.$data['location']['country']
                .' '.$data['location']['county'].' '.$data['location']['street'];
        }

        if ($loanType === 3) { // construction
            if ($data['investment_details']['propType'] === Deal::MIXED_USE) { // mixed use
                //$assetTypes[] = $data['investment_details']['mixedUse'];
                $isMixed = true;
                $assetTypes[] = Deal::MIXED_USE;
            } else {
                $assetTypes[] = $data['investment_details']['propType'];
                $multifamilyAmount = $data['investment_details']['propType'] === Deal::MULTIFAMILY ? $data['construction']['amount_units'] : 0;
            }
            $loanAmount = $data['construction_loan']['loanAmount'];
            $assetTypes[] = 5;
        }
        if ($loanType == 2) {
            if ($data['existing']['propertyType'] == 'OWNER_OCUPIED' || $data['existing']['propertyType'] == 'OWNER_OCCUPIED') { // :))
                $propertyType = 2;
            }
            if ($data['existing']['propertyType'] == 'INVESTMENT') { // :))
                $propertyType = 1;
            }
            if ($data['investment_details']['propType'] === Deal::MIXED_USE) { // mixed use
                // $assetTypes[] = $data['investment_details']['mixedUse'];
                $isMixed = true;
                $assetTypes[] = Deal::MIXED_USE;
            } else {
                $assetTypes[] = $propertyType === 2 ? 6 : $data['investment_details']['propType']; //$data['investment_details']['propType'];
                if ($data['investment_details']['propType'] === Deal::MULTIFAMILY) {
                    $multifamilyAmount = $data['investment_details']['numberUnit'];
                }
            }
            $loanAmount = $data['refinance_loan']['loanAmount'];
        }
        if ($loanType == 1) {
            if ($data['property_type'] === 3) {
                $assetTypes[] = 5;
            }
            if ($data['investment_details']['propType'] === Deal::MIXED_USE) { // mixed use
                // $assetTypes[] = $data['investment_details']['mixedUse'];
                $isMixed = true;
                $assetTypes[] = Deal::MIXED_USE;
            } else {
                $assetTypes[] = $data['property_type'] === 2 ? 6 : $data['investment_details']['propType'];
                if ($data['investment_details']['propType'] === Deal::MULTIFAMILY) {
                    $multifamilyAmount = $data['property_type'] === 3 ? $data['construction']['amount_units'] : $data['investment_details']['numberUnit'];
                }
            }

            $loanAmount = $data['purchase_loan']['loan_amount'];
        }

        if ($data['sponsor']['sponsorInfo'][0]['name'] !== '') {
            $sponsorName = ucfirst(strtolower($data['sponsor']['sponsorInfo'][0]['name']));
        }
        $lastKey = count($data['sponsor']['sponsorInfo']) - 1;
        foreach ($data['sponsor']['sponsorInfo'] as $key => $value) {
            $con = $lastKey === $key ? '' : ',';

            if ($value['name'] !== '') {
                $sponsorNames .= ucfirst(strtolower($value['name'])).$con;
            }
        }

        if ($data['finished']) {
            $mainType = $this->checkPropertyType($data);
            $deal = Deal::find($data['id']);
            $deal->update(['dollar_amount' => $loanAmount]);
            $deal->update(['location' => strtolower($location)]);
            $deal->update(['sponsor_name' => ucfirst($sponsorName)]);
            $deal->update(['main_type' => $mainType]);

            if (isset($data['type_of_loans']) && ! empty($data['type_of_loans'])) {
                foreach ($data['type_of_loans'] as $typeOfLoan) {
                    $hasTypeOfLoan = $deal->checkIfAlreadyStoredTypeOfLoan($typeOfLoan);
                    if ($hasTypeOfLoan->isNotEmpty()) {
                        continue;
                    }
                    $deal->storeDealTypeOfLoan($typeOfLoan);
                }
            }

            // Check if already has saved asset type
            $dealHasAttachAssetTypes = $deal->assetTypes()->get();
            if ($dealHasAttachAssetTypes->isEmpty()) {
                foreach ($assetTypes as $type) {
                    $deal->assetTypes()->attach($type);
                }
            }
        }

        $i = [
            'loan_type' => $loanType,
            'main_type' => $mainType,
            'multifamilyAmount' => $multifamilyAmount,
            'property_type' => [
                'type' => $propertyType,
                'asset_types' => $assetTypes,
                'mixed' => $isMixed,
            ],
            'loan_amount' => $loanAmount,
            'location' => strtolower($location),
            'sponsorName' => $sponsorName,
            'sponsorNames' => $sponsorNames,
        ];
        $data['property_type'] = $propertyType;
        $data['loan_type'] = $loanType;
        $data['inducted'] = $i;
        $data['main_type'] = $mainType;

        return $data;
    }

    public function checkPropertyType($mappedDeal)
    {
        $assetTypes = $mappedDeal['inducted']['property_type']['asset_types'];
        $isMixed = $mappedDeal['inducted']['property_type']['mixed'];

        if ($isMixed) {
            $type = Deal::MIXED_USE;
        } elseif (in_array(Deal::OWNER_OCCUPIED, $assetTypes)) {
            $type = Deal::OWNER_OCCUPIED;
        } elseif (in_array(Deal::CONSTRUCTION, $assetTypes)) {
            array_splice($assetTypes, array_search(Deal::CONSTRUCTION, $assetTypes), 1);
            $type = $assetTypes[0];
        } elseif ($assetTypes) {
            $type = $assetTypes[0];
        } else {
            $type = 0;
        }

        return $type;
    }

    /**
     * Mapped date when deal is finished or updated
     *
     * @return string
     */
    public function dateFinishedDeal($id): string
    {
        if (is_string($id)) {
            return (new Carbon($id, 'America/New_York'))->format('m/d/Y');
        }

        $dealEloquent = Deal::find($id);
        if (! $dealEloquent || (! $dealEloquent->finished_at && ! $dealEloquent->updated_at)) {
            $now = date('m/d/Y, h:i A', strtotime(now()->setTimezone('America/New_York')->format('m/d/Y')));

            return date('m/d/Y, h:i A', strtotime($now));
        } else {
            $user = User::find($dealEloquent->user_id);
            //Format time to PM or AM
            return $dealEloquent->finished_at ? $dealEloquent->finished_at->setTimezone($user->timezone)->format('m/d/Y, h:i A') : $dealEloquent->updated_at->setTimezone($user->timezone)->format('m/d/Y, h:i A');
        }
    }

    protected function compose_hard($user, $args, $ignored = [])
    {
        if (! $this->obj->user_id) {
            $this->obj->user_id = $user->id;
        }

        if (! $this->obj->finished) {
            $this->obj->finished = $args['finished'] ?? false;
        }

        if (isset($args['lastStepStatus'])) {
            $this->obj->lastStepStatus = $args['lastStepStatus'];
        }
    }

    private function getInductedEmpty()
    {
        return [
            'loan_type' => 0,
            'main_type' => 0,
            'multifamilyAmount' => 0,
            'property_type' => [
                'type' => 0,
                'asset_types' => [],
                'mixed' => false,
            ],
            'loan_amount' => 0,
            'location' => '',
            'sponsorName' => '',
            'sponsorNames' => '',
        ];
    }

    /**
     * @return array
     */
    public function getEmptyDeal()
    {
        $emptyDeal = $this->dataUnit();
        $emptyDeal['inducted'] = $this->getInductedEmpty();

        return $emptyDeal;
    }

    /**
     * @param $data
     * @return int
     */
    public function getTypeDealCalculate($data): int
    {
        $loanType = (int) $data['loan_type'];
        $checkType = 0;
        if ($loanType == 2 && $data['existing']['propertyType'] == 'INVESTMENT') {
            $checkType = Deal::INVESTMENT_PURCHASE_REFINANCE;
        }
        if ($data['existing']['propertyType'] == 'OWNER_OCUPIED' || $data['existing']['propertyType'] == 'OWNER_OCCUPIED' && $loanType == 2) {
            $checkType = Deal::OWNER_OCCUPIED_PURCHASE_REFINANCE;
        }
        if ($loanType == 1 && $data['property_type'] === 1) {
            $checkType = Deal::INVESTMENT_PURCHASE_REFINANCE;
        }
        if ($loanType == 1 && $data['property_type'] === 2) {
            $checkType = Deal::OWNER_OCCUPIED_PURCHASE_REFINANCE;
        }

        return $checkType;
    }
}
