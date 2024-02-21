<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Deal;

use App\AssetTypes;
use App\DataTransferObjects\DealMapper;
use App\Deal;
use App\DealEmail;
use App\Quote;
use App\Termsheet;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class IndividualAllData
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class IndividualAllData extends DealMapper
{
    protected $data;

    /**
     * @param  null  $obj
     * @return array
     *
     * Map data for individual deal
     */
    public function mapFromEloquent($obj = null): array
    {
        $this->data = parent::mapFromEloquent($this->obj);
        $deal = $this->obj;
        $email = DealEmail::where('deal_id', $deal->id)->pluck('email')->toArray();
        $emails = array_unique($email);
        $emails_field = [
            [], [], [],
        ];
        $emails_length = 0;
        $emails_index = 0;
        foreach ($emails as $e) {
            $emails_length += strlen($e);
            if ($emails_length > 40000) {
                $emails_index++;
                $emails_field[$emails_index] = [$e];
                $emails_length = 0;
            } elseif ($emails_index == 0 && count($emails_field[0]) < 1) {
                $emails_field[$emails_index] = [$e];
            } else {
                array_push($emails_field[$emails_index], $e);
            }
        }
        $allData = [
            $deal->broker->name(),
            $deal->broker->email,
            $deal->id,
            $this->dateFinishedDeal($deal->id),
            implode(', ', $emails_field[0]),
            implode(', ', $emails_field[1]),
            implode(', ', $emails_field[2]),
            $this->lenderWhoSkipped($deal),
            $deal->quotes->where('finished', true)->count(),
            $this->lenderWhoQuoted($deal),
            $this->quoteAccepted($deal),
            Termsheet::find($deal->termsheet)->title ?? '',

            $deal->data['location']['street_address'],
            $deal->data['location']['street_address_2'] ?? '',
            $deal->data['location']['city'],
            $deal->data['location']['state'],
            $deal->data['location']['zip_code'],
            $deal->data['block_and_lot']['block'],
            $deal->data['block_and_lot']['lot'],

            $this->formatDollarAmountDeal($deal->data['purchase_loan']['loan_amount']),
            $deal->data['purchase_loan']['days_to_close'] ?? '',
            $this->formatDollarAmountDeal($deal->data['purchase_loan']['price']),
            $this->formatDollarAmountDeal($deal->data['purchase_loan']['estimated_value']),
            $deal->data['purchase_loan']['estimated_cap_rate'],
            $deal->data['purchase_loan']['ltc_purchase'] ?? '',
            $this->checkBoolean($deal->data['show_address_purchase']),

            $this->formatDollarAmountDeal($deal->data['refinance_loan']['loanAmount']),
            $deal->data['refinance_loan']['date'] ?? '',
            $this->formatDollarAmountDeal($deal->data['refinance_loan']['purchasePrice']),
            $this->formatDollarAmountDeal($deal->data['refinance_loan']['currentValue']),
            $deal->data['refinance_loan']['list'] ?? '',

            ucfirst(strtolower(Deal::LOAN_TYPE[$deal->data['loan_type']])),
            $deal->getNamesTypeOfLoans(),
            ucfirst(strtolower(str_replace('_', ' ', Deal::PROPERTY_TYPE[$deal->data['property_type']]))),
            AssetTypes::find($deal->main_type)->title ?? '',

            $deal->data['owner_occupied']['business_name'] ?? '',
            $deal->data['owner_occupied']['business_description'] ?? '',
            $this->formatDollarAmountDeal($deal->data['owner_occupied']['sales_amount']),
            $this->formatDollarAmountDeal($deal->data['owner_occupied']['profit_amount']),
            $deal->data['owner_occupied']['borrower_own'] ? $deal->data['owner_occupied']['borrower_own'].'%' : '',
            $deal->data['owner_occupied']['business_age'] ?? '',
            $this->formatDollarAmountDeal($deal->data['owner_occupied']['sales_amount_YTD']),
            $this->formatDollarAmountDeal($deal->data['owner_occupied']['profit_amount_YTD']),
            $deal->data['owner_occupied']['employees'] ?? '',

            $this->mixedUseData($deal),
            $deal->data['investment_details']['retailType'],
            $deal->data['investment_details']['retailAmount'],
            $deal->data['investment_details']['retailNumberOfUnitsOccupied'],
            $deal->data['investment_details']['retailSquare'],
            $deal->data['investment_details']['retailSquareFootageOccupied'],
            $deal->data['investment_details']['multiAmount'],
            $deal->data['investment_details']['multiNumberOfUnitsOccupied'],
            $deal->data['investment_details']['multiSquare'],
            $deal->data['investment_details']['multiSquareFootageOccupied'],
            $deal->data['investment_details']['officeAmount'],
            $deal->data['investment_details']['officeNumberOfUnitsOccupied'],
            $deal->data['investment_details']['officeSquare'],
            $deal->data['investment_details']['officeSquareFootageOccupied'],
            $deal->data['investment_details']['warehouseAmount'],
            $deal->data['investment_details']['warehouseNumberOfUnitsOccupied'],
            $deal->data['investment_details']['warehouseSquare'],
            $deal->data['investment_details']['warehouseSquareFootageOccupied'],
            $deal->data['investment_details']['numberUnit'] ?? '',
            $deal->data['investment_details']['numberUnitOccupied'] ?? '',
            $deal->data['investment_details']['squareFootage'] ?? '',
            $deal->data['investment_details']['squareFootageOccupied'] ?? '',
            $deal->data['investment_details']['proposedUse'] ?? '',
            $deal->data['investment_details']['noteToLender'] ?? '',
            $deal->data['investment_details']['amountOfUnits'] ?? '',
            $deal->data['investment_details']['rentableSellable'] ?? '',
            $deal->data['investment_details']['retailFloors'] ?? '',
            $deal->data['investment_details']['multiAmountOfUnits'] ?? '',
            $deal->data['investment_details']['multiRentableSellable'] ?? '',
            $deal->data['investment_details']['multiFloors'] ?? '',
            $deal->data['investment_details']['officeAmountOfunits'] ?? '',
            $deal->data['investment_details']['officeRentableSellable'] ?? '',
            $deal->data['investment_details']['officeFloors'] ?? '',
            $deal->data['investment_details']['industrialAmountOfUnits'] ?? '',
            $deal->data['investment_details']['industrialRentableSellable'] ?? '',
            $deal->data['investment_details']['industrialFloors'] ?? '',

            $this->formatDollarAmountDeal($deal->data['construction_loan']['loanAmount']),
            $this->checkBoolean($deal->data['construction_loan']['buying_land']),
            $this->checkBoolean($deal->data['construction_loan']['debt_on_property']),
            $this->formatDollarAmountDeal($deal->data['construction_loan']['purchase_price']),
            $deal->data['construction_loan']['purchase_date'] ?? '',
            $this->formatDollarAmountDeal($deal->data['construction_loan']['debt_amount']),
            $deal->data['construction_loan']['lender_name'] ?? '',
            $this->checkBoolean($deal->data['construction_loan']['show_address_construction']),

            $this->formatDollarAmountDeal($deal->data['construction']['land_cost']),
            $this->formatDollarAmountDeal($deal->data['construction']['hard_cost']),
            $this->formatDollarAmountDeal($deal->data['construction']['soft_cost']),
            $this->formatDollarAmountDeal($deal->data['construction']['current_value']),
            $deal->data['construction']['date'] ?? '',

            $deal->data['construction']['contractor_name'] ?? '',
            $deal->data['construction']['amount_units'] ?? '',
            $deal->data['construction']['square_footage'] ?? '',
            $deal->data['construction']['floors'] ?? '',

            $this->sellOrRent($deal->data['construction']['plans']),
            $deal->data['construction']['projections'] ?? '',
            $this->formatDollarAmountDeal($deal->data['construction']['projections_sales']),
            $this->formatDollarAmountDeal($deal->data['construction']['projections_per_units']),
            $this->formatDollarAmountDeal($deal->data['construction']['projections_per_sf']),
            $deal->data['construction']['rental_per'] ?? '',
            $this->formatDollarAmountDeal($deal->data['construction']['rental_amount']),
            isset($deal->data['construction']['rental_projections_per_units']) ? $this->formatDollarAmountDeal($deal->data['construction']['rental_projections_per_units']) : '',
            isset($deal->data['construction']['rental_projections_per_sf']) ? $this->formatDollarAmountDeal($deal->data['construction']['rental_projections_per_sf']) : '',

        ];

        $allData12 = $this->projectionMixedUse($deal);

        $allData = array_merge($allData, $allData12);

        $allData2 = [

            $this->checkExistingFree($deal->data['existing']['free']),
            $deal->data['existing']['lender'] ?? '',
            $this->formatDollarAmountDeal($deal->data['existing']['warehouse']),

            $this->formatDollarAmountDeal($deal->data['expenses']['taxNumber']),
            $this->checkBoolean($deal->data['expenses']['tax']),
            $deal->data['expenses']['expDate'] ?? '',
            $deal->data['expenses']['phaseStructure'] ?? '',
            $this->formatDollarAmountDeal($deal->data['expenses']['insurance']),
            $this->formatDollarAmountDeal($deal->data['expenses']['repairs']),
            $this->formatDollarAmountDeal($deal->data['expenses']['payrollAmount']),
            $this->checkExpenses($deal->data['expenses']['payroll']),
            $this->formatDollarAmountDeal($deal->data['expenses']['electricityAmount']),
            $deal->data['expenses']['electricity'] === 'true' ? 'Paid by tenant' : '',
            $this->checkSeparatelyMetered($deal->data['expenses']['electricity'], $deal->data['expenses']['electricitySeparatelyMetered']),
            $this->formatDollarAmountDeal($deal->data['expenses']['gasAmount']),
            $deal->data['expenses']['gas'] === 'true' ? 'Paid by tenant' : '',
            $this->checkSeparatelyMetered($deal->data['expenses']['gas'], $deal->data['expenses']['gasSeparatelyMetered']),
            $this->formatDollarAmountDeal($deal->data['expenses']['waterAmount']),
            $deal->data['expenses']['water'] === 'true' ? 'Paid by tenant' : '',
            $this->checkSeparatelyMetered($deal->data['expenses']['water'], $deal->data['expenses']['waterSeparatelyMetered']),
            $this->formatDollarAmountDeal($deal->data['expenses']['elevatorMaintenanceAmount']),
            $this->checkExpenses($deal->data['expenses']['elevatorMaintenance']),
            $this->formatDollarAmountDeal($deal->data['expenses']['ooWaterAmount']),
            $this->formatDollarAmountDeal($deal->data['expenses']['ooSewerAmount']),
            $this->formatDollarAmountDeal($deal->data['expenses']['legal']),
            $this->formatDollarAmountDeal($deal->data['expenses']['commonAreaAmount']),
            $this->checkExpenses($deal->data['expenses']['commonArea']),
            $this->checkManagement($deal->data['expenses']['management'], $deal->data['expenses']['managementAmount']),
            $deal->data['expenses']['managementCompanyName'] ?? '',
            $this->checkBoolean($deal->data['expenses']['triple']),
            $this->formatDollarAmountDeal($deal->data['expenses']['reimbursement']),
            $this->formatOtherExpenses($deal->data['expenses']['otherExpenses']),
            $deal->data['expenses']['additionalNotes'] ?? '',
            $this->formatDollarAmountDeal($deal->data['expenses']['totalExpenses']),
            $this->formatDollarAmountDeal($deal->data['expenses']['totalBusinessOperatingIncome']),
            $this->formatDollarAmountDeal($deal->data['expenses']['netOperatingIncome']),

            $this->getSponsorsName($deal->data['sponsor']['sponsorInfo']),
            $this->getSponsorsFields($deal->data['sponsor']['sponsorInfo'], 'ownership'),
            $this->getSponsorsFields($deal->data['sponsor']['sponsorInfo'], 'years_experience'),
            // $deal->data['sponsor']['sponsorsInfo']['years_experience'] ?? '',
            $this->getSponsorsFields($deal->data['sponsor']['sponsorInfo'], 'family_experience', 'boolean'),
            $this->getSponsorsFields($deal->data['sponsor']['sponsorInfo'], 'annual_income', 'amount'),
            $this->getSponsorsFields($deal->data['sponsor']['sponsorInfo'], 'annual_expenses', 'amount'),
            $this->getSponsorsFields($deal->data['sponsor']['sponsorInfo'], 'liabilities', 'amount'),
            $this->getSponsorsFields($deal->data['sponsor']['sponsorInfo'], 'assets_real_estate', 'amount'),
            $this->getSponsorsFields($deal->data['sponsor']['sponsorInfo'], 'assets_companies', 'amount'),
            $this->getSponsorsFields($deal->data['sponsor']['sponsorInfo'], 'assets_other', 'amount'),
            $this->getSponsorsFields($deal->data['sponsor']['sponsorInfo'], 'assets_liquid', 'amount'),
            $this->getSponsorsFields($deal->data['sponsor']['sponsorInfo'], 'net_worth', 'amount'),
            $this->getSponsorsFields($deal->data['sponsor']['sponsorInfo'], 'net_income', 'amount'),
            $this->getSponsorsFields($deal->data['sponsor']['sponsorInfo'], 'total_assets', 'amount'),

            $this->groosRentRoll('annual_income', $deal),
            $this->groosRentRoll('occupiedGroos', $deal),
            $this->groosRentRoll('vacancy', $deal),
            // $deal->data['rent_roll']['annual_income'] ? $this->formatDollarAmountDeal($deal->data['rent_roll']['annual_income']) : '',
            // $deal->data['rent_roll']['occupiedGroos'] ? $this->formatDollarAmountDeal($deal->data['rent_roll']['occupiedGroos']) : '',
            // $deal->data['rent_roll']['vacancy'] ? $this->formatDollarAmountDeal($deal->data['rent_roll']['vacancy']) : '',

            $deal->data['rent_roll']['annual_sf_total'] ? $this->formatDollarAmountDeal($deal->data['rent_roll']['annual_sf_total']) : '',
            $deal->data['rent_roll']['potential_income'],
            $deal->data['rent_roll']['increaseProjection'],
            $deal->data['rent_roll']['timeFrame'] && intval($this->data['rent_roll']['timeFrame']) >= 1 ? (intval($this->data['rent_roll']['timeFrame']) > 1 ? $this->data['rent_roll']['timeFrame'].' months' : $this->data['rent_roll']['timeFrame'].' month') : '',
            $deal->data['rent_roll']['plannedImprovements'] ?? '',
            $deal->data['rent_roll']['increasedNotes'] ?? '',
            $deal->data['rent_roll']['betterNotes'] ?? '',
            $this->formatDollarAmountDeal($deal->data['rent_roll']['capExBudget']),
            $this->formatDollarAmountDeal($deal->data['rent_roll']['tiBudget']),
            $this->formatDollarAmountDeal($deal->data['rent_roll']['lcBudget']),
            $this->formatDollarAmountDeal($deal->data['rent_roll']['monthle_total']),
            $this->formatDollarAmountDeal($deal->data['rent_roll']['annual_total']),
            $this->formatOtherIncome($deal->data['rent_roll']['other_income']),
            $this->formatDollarAmountDeal($deal->data['rent_roll']['totalIncome']),
        ];

        $allData = array_merge($allData, $allData2);

        $rentRoll = $this->mapRentRoll($deal->data['rent_roll']['table']);

        if (! empty($rentRoll)) {
            $allData = array_merge($allData, $rentRoll);
        }

        return $allData;
    }

    private function projectionMixedUse($deal)
    {
        $arr = [];
        $retalIndex = -1;
        $multifamilyIndex = -1;
        $officeIndex = -1;
        $industrialIndex = -1;
        foreach ($deal->data['construction']['projectionMixedUse'] as $key => $obj) {
            if ($obj['tag'] === 'RETAIL') {
                $retalIndex = $key;
            } elseif ($obj['tag'] === 'MULTIFAMILY') {
                $multifamilyIndex = $key;
            } elseif ($obj['tag'] === 'OFFICE') {
                $officeIndex = $key;
            } else {
                $industrialIndex = $key;
            }
        }

        if ($retalIndex !== -1) {
            $arr[] = $this->sellOrRent($deal->data['construction']['projectionMixedUse'][$retalIndex]['plans']) ?? '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$retalIndex]['projections'] ?? '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$retalIndex]['projections_sales'] ? '$'.$deal->data['construction']['projectionMixedUse'][$retalIndex]['projections_sales'] : '';

            $arr[] = $deal->data['construction']['projectionMixedUse'][$retalIndex]['projections_per_units'] ? '$'.$deal->data['construction']['projectionMixedUse'][$retalIndex]['projections_per_units'] : '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$retalIndex]['projections_per_sf'] ? '$'.$deal->data['construction']['projectionMixedUse'][$retalIndex]['projections_per_sf'] : '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$retalIndex]['rental_per'] ?? '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$retalIndex]['rental_amount'] ? '$'.$deal->data['construction']['projectionMixedUse'][$retalIndex]['rental_amount'] : '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$retalIndex]['rental_projections_per_units'] ? '$'.$deal->data['construction']['projectionMixedUse'][$retalIndex]['rental_projections_per_units'] : '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$retalIndex]['rental_projections_per_sf'] ? '$'.$deal->data['construction']['projectionMixedUse'][$retalIndex]['rental_projections_per_sf'] : '';
        } else {
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
        }

        if ($multifamilyIndex !== -1) {
            $arr[] = $this->sellOrRent($deal->data['construction']['projectionMixedUse'][$multifamilyIndex]['plans']) ?? '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$multifamilyIndex]['projections'] ?? '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$multifamilyIndex]['projections_sales'] ? '$'.$deal->data['construction']['projectionMixedUse'][$multifamilyIndex]['projections_sales'] : '';

            $arr[] = $deal->data['construction']['projectionMixedUse'][$multifamilyIndex]['projections_per_units'] ? '$'.$deal->data['construction']['projectionMixedUse'][$multifamilyIndex]['projections_per_units'] : '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$multifamilyIndex]['projections_per_sf'] ? '$'.$deal->data['construction']['projectionMixedUse'][$multifamilyIndex]['projections_per_sf'] : '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$multifamilyIndex]['rental_per'] ?? '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$multifamilyIndex]['rental_amount'] ? '$'.$deal->data['construction']['projectionMixedUse'][$multifamilyIndex]['rental_amount'] : '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$multifamilyIndex]['rental_projections_per_units'] ? '$'.$deal->data['construction']['projectionMixedUse'][$multifamilyIndex]['rental_projections_per_units'] : '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$multifamilyIndex]['rental_projections_per_sf'] ? '$'.$deal->data['construction']['projectionMixedUse'][$multifamilyIndex]['rental_projections_per_sf'] : '';
        } else {
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
        }

        if ($officeIndex !== -1) {
            $arr[] = $this->sellOrRent($deal->data['construction']['projectionMixedUse'][$officeIndex]['plans']) ?? '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$officeIndex]['projections'] ?? '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$officeIndex]['projections_sales'] ? '$'.$deal->data['construction']['projectionMixedUse'][$officeIndex]['projections_sales'] : '';

            $arr[] = $deal->data['construction']['projectionMixedUse'][$officeIndex]['projections_per_units'] ? '$'.$deal->data['construction']['projectionMixedUse'][$officeIndex]['projections_per_units'] : '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$officeIndex]['projections_per_sf'] ? '$'.$deal->data['construction']['projectionMixedUse'][$officeIndex]['projections_per_sf'] : '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$officeIndex]['rental_per'] ?? '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$officeIndex]['rental_amount'] ? '$'.$deal->data['construction']['projectionMixedUse'][$officeIndex]['rental_amount'] : '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$officeIndex]['rental_projections_per_units'] ? '$'.$deal->data['construction']['projectionMixedUse'][$officeIndex]['rental_projections_per_units'] : '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$officeIndex]['rental_projections_per_sf'] ? '$'.$deal->data['construction']['projectionMixedUse'][$officeIndex]['rental_projections_per_sf'] : '';
        } else {
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
        }

        if ($industrialIndex !== -1) {
            $arr[] = $this->sellOrRent($deal->data['construction']['projectionMixedUse'][$industrialIndex]['plans']) ?? '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$industrialIndex]['projections'] ?? '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$industrialIndex]['projections_sales'] ? '$'.$deal->data['construction']['projectionMixedUse'][$industrialIndex]['projections_sales'] : '';

            $arr[] = $deal->data['construction']['projectionMixedUse'][$industrialIndex]['projections_per_units'] ? '$'.$deal->data['construction']['projectionMixedUse'][$industrialIndex]['projections_per_units'] : '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$industrialIndex]['projections_per_sf'] ? '$'.$deal->data['construction']['projectionMixedUse'][$industrialIndex]['projections_per_sf'] : '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$industrialIndex]['rental_per'] ?? '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$industrialIndex]['rental_amount'] ? '$'.$deal->data['construction']['projectionMixedUse'][$industrialIndex]['rental_amount'] : '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$industrialIndex]['rental_projections_per_units'] ? '$'.$deal->data['construction']['projectionMixedUse'][$industrialIndex]['rental_projections_per_units'] : '';
            $arr[] = $deal->data['construction']['projectionMixedUse'][$industrialIndex]['rental_projections_per_sf'] ? '$'.$deal->data['construction']['projectionMixedUse'][$industrialIndex]['rental_projections_per_sf'] : '';
        } else {
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
            $arr[] = '';
        }

        return $arr;
    }

    private function groosRentRoll($label, $deal)
    {
        switch ($label) {
            case 'annual_income':
                if ($deal->data['rent_roll']['vacancy'] === '') {
                    return '';
                } else {
                    return $deal->data['rent_roll']['annual_income'] ? $this->formatDollarAmountDeal($deal->data['rent_roll']['annual_income']) : '';
                }
                // no break
            case 'occupiedGroos':
                if ($deal->data['rent_roll']['vacancy'] === '') {
                    return $deal->data['rent_roll']['annual_income'] ? $this->formatDollarAmountDeal($deal->data['rent_roll']['annual_income']) : '';
                }

                return $deal->data['rent_roll']['occupiedGroos'] ? $this->formatDollarAmountDeal($deal->data['rent_roll']['occupiedGroos']) : '';
            case 'vacancy':
                $vacancy = $this->formatDollarAmountDeal($deal->data['rent_roll']['vacancy']);

                return $deal->data['rent_roll']['vacancy'] ? '-'.$vacancy : '';
        }
    }

    private function sellOrRent($plans)
    {
        if ($plans === '') {
            return $plans;
        }

        if ($plans === 'sell') {
            return 'Sell the units';
        } else {
            return 'Rent out the units';
        }
    }

    /**
     * @param $deal
     * @return string
     *
     * Return emails of lenders who skipped the deal
     */
    private function lenderWhoSkipped($deal): string
    {
        $lender_ids = $deal->checkUserDeal(Deal::IGNORE_DEAL)->select('user_id')->get();
        $lender_emails = User::where('role', 'lender')->whereIn('id', $lender_ids)->select('email')->get();
        if ($lender_emails->isNotEmpty()) {
            $emails = $lender_emails->pluck('email')->toArray();

            return implode(', ', $emails);
        }

        return '';
    }

    /**
     * @param $deal
     * @return string
     *
     * Return emails of lenders who quoted deal
     */
    private function lenderWhoQuoted($deal): string
    {
        $quote_ids = $deal->quotes()->where('finished', true)->select('user_id')->get();
        $lenderWhoQuoted = DB::table('users')->select('users.email')
            ->join('quotes', 'users.id', '=', 'quotes.user_id')
            ->whereIn('quotes.user_id', $quote_ids)
            ->distinct()->get();
        if ($lenderWhoQuoted->isNotEmpty() && $quote_ids->isNotEmpty()) {
            $emails = $lenderWhoQuoted->pluck('email')->toArray();

            return implode(', ', $emails) ?? '';
        }

        return '';
    }

    /**
     * @param $deal
     * @return string
     *
     * Lender which quote was accepted
     */
    private function quoteAccepted($deal): string
    {
        $quote = $deal->quotes()->where('status', Quote::ACCEPTED)->select('id', 'user_id')->first();
        $secondQuote = $deal->quotes()->where('status', Quote::SECOND_ACCEPTED)->select('id', 'user_id')->first();
        if ($quote && $secondQuote) {
            $lender_email = User::find($quote->user_id)->email;
            $lender_email_second = User::find($secondQuote->user_id)->email;

            return 'Quote #'.$quote->id.' by '.$lender_email.'; Quote #'.$secondQuote->id.' by '.$lender_email_second;
        } elseif ($quote) {
            Log::info($quote);
            $lender_email = User::find($quote->user_id)->email;

            return 'Quote #'.$quote->id.' by '.$lender_email;
        }

        return '';
    }

    /**
     * @param $amount
     * @return string|string[]
     *
     * Format dollar amount
     */
    private function formatDollarAmountDeal($amount): string
    {
        if ($amount !== 0 || $amount !== '0') {
            $fmt = new \NumberFormatter('en-US', \NumberFormatter::CURRENCY);
            $amountDollar = $fmt->formatCurrency((float) $amount, 'USD');
            $formatAmount = str_replace('.00', '', $amountDollar);
            if ($formatAmount === '$0') {
                return '';
            }

            return $formatAmount;
        }

        return '';
    }

    /**
     * @param $deal
     * @return string
     *
     * Mapped mixed use asset types
     */
    private function mixedUseData($deal): string
    {
        $mixed = $deal->data['investment_details']['mixedUse'];
        if (! empty($mixed)) {
            $mixedAssetTypes = array_map(function ($mix) {
                return AssetTypes::find($mix)->title;
            }, $mixed);

            return implode(', ', $mixedAssetTypes) ?? '';
        }

        return '';
    }

    /**
     * @param $answer
     * @return string
     */
    private function checkBoolean($answer): string
    {
        if (! $answer) {
            return '';
        }
        if ($answer === 'true' || $answer === true) {
            return 'Yes';
        }

        return 'No';
    }

    /**
     * @param $answer
     * @return string
     */
    private function checkExistingFree($answer): string
    {
        if (! $answer) {
            return '';
        }
        if ($answer === 'true' || $answer === true) {
            return ' Free and Clear - no loans';
        }

        return 'Yes';
    }

    /**
     * @param $answer
     * @return string
     */
    private function checkExpenses($answer): string
    {
        if (! $answer || $answer === 'false') {
            return '';
        }
        if ($answer === 'true') {
            return 'n/a';
        }

        return '';
    }

    /**
     * @param $check
     * @param $separatelyMetered
     * @return string
     */
    private function checkSeparatelyMetered($check, $separatelyMetered): string
    {
        if (! $check || $check === 'false') {
            return '';
        }
        if ($check === 'true' && $separatelyMetered === 'true') {
            return 'Yes';
        }

        return 'No';
    }

    /**
     * @param $management
     * @param $managementAmount
     * @return string|string[]
     */
    private function checkManagement($management, $managementAmount)
    {
        if ($management && $management === 'true') {
            return 'Self management';
        }

        return $this->formatDollarAmountDeal($managementAmount);
    }

    /**
     * @param $sponsors
     * @return string
     */
    private function getSponsors($sponsors): string
    {
        $sponsorsMapped = array_map(function ($sponsor) {
            if ($sponsor['ownership']) {
                return $sponsor['name'].' '.$sponsor['ownership'].'%';
            }

            return $sponsor['name'];
        }, $sponsors);

        return implode(', ', $sponsorsMapped);
    }

    private function getSponsorsName($sponsors): string
    {
        $sponsorsMapped = array_map(function ($sponsor) {
            return $sponsor['name'];
        }, $sponsors);

        return implode(', ', $sponsorsMapped);
    }

    private function getSponsorsFields($sponsors, $field, $type = null): string
    {
        $sponsorsMapped = array_map(function ($sponsor) use ($field, $type) {
            if (array_key_exists($field, $sponsor)) {
                if ($type === 'amount') {
                    return $this->formatDollarAmountDeal($sponsor[$field]);
                }
                if ($type === 'boolean') {
                    if (trim($sponsor[$field]) === '') {
                        return null;
                    } elseif (($sponsor[$field]) > 5) {
                        return null;
                    } else {
                        if ($sponsor[$field] === 'true') {
                            return $sponsor[$field] = 'Yes';
                        } else {
                            return $sponsor[$field] = 'No';
                        }
                    }
                }

                return $sponsor[$field];
            }
        }, $sponsors);

        $sponsorsMapped = array_filter($sponsorsMapped);

        return implode(', ', $sponsorsMapped);
    }

    /**
     * @param $enum
     * @return string
     */
    private function formatEnum($enum): string
    {
        if ($enum !== 0) {
            $enumValue = Deal::PROJECTION_INCREASE_REASONS[$enum];
            $str = str_replace('_', ' ', $enumValue);

            return str_replace('\' ', '\'', ucwords(str_replace('\'', '\' ', strtolower($str))));
        }

        return '';
    }

    private function formatOtherExpenses($otherExpenses): string
    {
        $otherExpensesMapped = array_map(function ($otherExpense) {
            return $otherExpense['type'].': '.$otherExpense['amount'];
        }, $otherExpenses);

        return implode('; ', $otherExpensesMapped);
    }

    private function formatOtherIncome($otherIncome): string
    {
        $semicolon = ': ';
        $otherIncomeMapped = array_map(function ($otherIncomeSingle) use ($semicolon) {
            if ($otherIncomeSingle['type'] === '' && $otherIncomeSingle['amount'] === '') {
                $semicolon = '';
            }

            return $otherIncomeSingle['type'].$semicolon.$otherIncomeSingle['amount'];
        }, $otherIncome);

        return implode('; ', $otherIncomeMapped);
    }

    /**
     * @param $table
     * @return array
     */
    private function mapRentRoll($table): array
    {
        $dataMapped = [];
        if (empty($table) || $table[0]['monthle_rent'] === '') {
            return $dataMapped;
        }
        foreach ($table as $row) {
            $rentRollRow = '';
            if ($row['occupied']) {
                $rentRollRow .= 'Occupied ,';
            } else {
                $rentRollRow .= 'Vacant ,';
            }
            if ($row['unit_type']) {
                $rentRollRow .= 'Unit Type: '.$row['unit_type'].',';
            }
            if ($row['name']) {
                $rentRollRow .= 'Tenant\'s name: '.$row['name'].',';
            }
            if ($row['unit']) {
                $rentRollRow .= 'Unit: '.$row['unit'].',';
            }
            if ($row['bedroom']) {
                $rentRollRow .= 'Bedroom/Bathroom: '.$row['bedroom'].',';
            }
            if ($row['lease_start']) {
                $rentRollRow .= 'Lease Start: '.$row['lease_start'].',';
            }
            if ($row['sf']) {
                $rentRollRow .= 'SF: '.$row['sf'].',';
            }
            if ($row['monthle_rent']) {
                $monthleRent = $this->formatDollarAmountDeal($row['monthle_rent']);
                $rentRollRow .= 'Monthly Rent: '.$monthleRent.',';
            }
            if ($row['annual_rent']) {
                $annualRent = $this->formatDollarAmountDeal($row['annual_rent']);
                $rentRollRow .= 'Annual Rent: '.$annualRent;
            }
            if ($row['annual_rent_sf']) {
                $annualRentsf = $this->formatDollarAmountDeal($row['annual_rent_sf']);
                $rentRollRow .= 'Annual Rent/SF: '.$annualRentsf;
            }

            $dataMapped[] = $rentRollRow;
        }

        return $dataMapped;
    }

    /**
     * @return string[]
     *
     * Return headings for Exel export all data
     */
    public function getHeadings(): array
    {
        return [
            'Broker name',
            'Broker email',
            'Deal id',
            'Date and Time Posted',
            'Lenders who received this deal - it "fit their preferences"',
            'Lenders who received this deal - it "fit their preferences"',
            'Lenders who received this deal - it "fit their preferences"',
            'Lenders who \'skipped\' this deal',
            'Quotes received',
            'Lender who quoted this deal',
            'Which quote was accepted?',
            'Status',

            'Street Address',
            'Street Address 2',
            'City',
            'State / Province',
            'Postal / Zip Code',
            'Block',
            'Lot',

            'Loan amount requested',
            'Days To Close',
            'Purchase Price',
            'Estimated Value (optional)',
            'Estimated Cap Rate (optional)',
            'LTC',
            'Hide Address',

            'Loan Amount Requested',
            'Date',
            'Initial purchase price',
            'Current value',
            'List improvements',

            'Asset Type',
            'Lender Type',
            'Property type',
            'Type',

            'Business name',
            'Business description',
            'Sales amount',
            'Profits amount',
            'Percent Borrower owns',
            'Business age',
            'Sales amount YTD',
            'Profits amount YTD',
            'Employees',

            'Mixed use type',
            'Retail type',
            'Retail number of units',
            'Retail number of units occupied',
            'Retail square footage',
            'Retail square footage occupied',
            'Multi number of units',
            'Multi number of units occupied',
            'Multi square footage',
            'Multi square footage occupied',
            'Office number of units',
            'Office number of units occupied',
            'Office square footage',
            'Office square footage occupied',
            'Industrial number of units',
            'Industrial number of units occupied',
            'Industrial square footage',
            'Industrial square footage occupied',
            'Number of units',
            'Number of units occupied',
            'Square footage',
            'Square footage occupied',
            'Proposed use',
            'Note to lender',
            'Retail Amount of Units',
            'Retail Rentable Sellable',
            'Retail Floors',
            'Multi Amount of Units',
            'Multi Rentable Sellable',
            'Multi Floors',
            'Office Amount of Units',
            'Office Rentable Sellable',
            'Office Floors',
            'Industrial Amount of Units',
            'Industrial Rentable Sellable',
            'Industrial Floors',

            'Loan Amount Requested',
            'Are you buying the land?',
            'Is there debt on the property?',
            'Initial purchase price',
            'Initial purchase date',
            'Dollar amount of Debt',
            'Lender name',
            'Hide Address ',

            'Land cost',
            'Hard costs',
            'Soft costs',
            'Current Land Value',
            'Date Purchased',

            'Contractor name',
            'Amount of units',
            'Rentable/Sellable SF',
            'Floors',

            'Planning',

            'Sales Projections per',
            'Sales Projections Amount',
            'Sales Projections per unit',
            'Sales Projections per S/F',
            'Rental Projections per',
            'Rental Projections amount',
            'Rental Projections per unit',
            'Rental Projections per S/F',

            'Retail Planning',

            'Retail Sales Projections per',
            'Retail Sales Projections Amount',
            'Retail Sales Projections per unit',
            'Retail Sales Projections per S/F',
            'Retail Rental Projections per',
            'Retail Rental Projections amount',
            'Retail Rental Projections per unit',
            'Retail Rental Projections per S/F',

            'Multifamily Planning',

            'Multifamily Sales Projections per',
            'Multifamily Sales Projections Amount',
            'Multifamily Sales Projections per unit',
            'Multifamily Sales Projections per S/F',
            'Multifamily Rental Projections per',
            'Multifamily Rental Projections amount',
            'Multifamily Rental Projections per unit',
            'Multifamily Rental Projections per S/F',

            'Office Planning',

            'Office Sales Projections per',
            'Office Sales Projections Amount',
            'Office Sales Projections per unit',
            'Office Sales Projections per S/F',
            'Office Rental Projections per',
            'Office Rental Projections amount',
            'Office Rental Projections per unit',
            'Office Rental Projections per S/F',

            'Indrustrial Planning',

            'Indrustrial Sales Projections per',
            'Indrustrial Sales Projections Amount',
            'Indrustrial Sales Projections per unit',
            'Indrustrial Sales Projections per S/F',
            'Indrustrial Rental Projections per',
            'Indrustrial Rental Projections amount',
            'Indrustrial Rental Projections per unit',
            'Indrustrial Rental Projections per S/F',

            'Are there existing loans on this property?',
            'Lender name',
            'Amount',

            'Real Estate taxes',
            'Is there a tax abatement?',
            'Expiration date',
            'Phase out structure',
            'Insurance',
            'Repairs & Maintenance',
            'Payroll and reserves',
            'Payroll and reserves',
            'Electricity',
            'Electricity (optional)',
            'Electricity separately metered?',
            'Gas',
            'Gas (optional)',
            'Gas separately metered?',
            'Water and Sewer',
            'Water and Sewer (optional)',
            'Water and Sewer separately metered?',
            'Elevator Maintenance',
            'Elevator Maintenance',
            'Water',
            'Sewer',
            'Legal and professional',
            'Common Area Utilities',
            'Common Area Utilities',
            'Management',
            'Management company name',
            'Are any expenses recovered?',
            'Expense Recoveries',
            'Any other expenses?',
            'Additional notes to lenders',
            'Total expenses',
            'Total business operating income',
            'Net operating income',

            'Sponsor name',
            'Ownership',
            'Years of experience?',
            'Family experience?',
            'Sponsor annual income',
            'Sponsor annual expenses',
            'Sponsor liabilities',
            'Sponsor assets Real estate',
            'Sponsor assets Companies/Corporations',
            'Sponsor assets Other Assets',
            'Sponsor assets Liquid Assets',

            'Sponsor Net Worth',
            'Sponsor Net Income',
            'Sponsor Total Assets',

            'Gross potential income',
            'Total In-Place Rental Income',
            'Vacancy',
            'Annual rent/SF',
            'Potential annual income',
            'Reasons for increased projections',
            'Time frame (months)',
            'Planned improvements',
            'Increased occupancy notes',
            'Better leases notes',
            'CapEx Budget',
            'TI Budget',
            'LC Budget',

            'Monthly total',
            'Annual Total',
            'Other Income',
            'Total Income',

            'RENT ROLL',
        ];
    }
}
