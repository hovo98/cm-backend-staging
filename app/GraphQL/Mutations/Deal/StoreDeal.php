<?php

namespace App\GraphQL\Mutations\Deal;

use App\Deal;
use App\Events\DealPublished;
use App\Exceptions\PaymentException;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class StoreDeal
{
    /**
     * Return a value for the field.
     *
     * @param  null  $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param  mixed[]  $args The arguments that were passed into the field.
     * @param  \Nuwave\Lighthouse\Support\Contracts\GraphQLContext  $context Arbitrary data that is shared between all fields of a single query.
     * @param  \GraphQL\Type\Definition\ResolveInfo  $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = User::find($context->user()->id);
        $dealId = $args['id'] ?? false;

        $isForced = Arr::has($args, 'force') && $args['force'] === true;

        $deal = null;

        // Check if quote is already finished
        if ($dealId) {
            $deal = Deal::find($dealId);

            if ($deal->finished) {
                throw new \Exception('This deal has already been published. To edit, go to the deal page.', 400);
            }
        }

        /**
         * Init
         */
        $userStatus = strtolower($user->getCompany()['company_status']);

        $finishApproved = true;
        if (isset($args['finished']) && $userStatus != 'approved') {
            $finishApproved = false;
        }

        if (! $finishApproved) {
            throw new \Exception('Denied, User or Company not approved to finish saving Deal', 403);
        }

        $deal = $deal ?? new Deal();

        if (!$isForced) {
            if ($dealId && isset($args['finished']) && $args['finished'] === true) {
                $deal = Deal::find($args['id']);
                if ($user->canAccept($deal) === false) {
                    throw new PaymentException("subscription_upgrade_required");
                }
            }
        }

        try {
            $mapper = new \App\DataTransferObjects\DealMapper($deal);
        } catch (\Exception $e) {
            Log::warning($e->getMessage());
            throw new \Exception($e->getMessage(), 400);
        }

        /**
         * Reset data of deal if flow is changed
         */
        if ($dealId) {
            $dealObj = Deal::find($dealId);
            // Check if deal flow is changed
            $dataType = $dealObj->checkDealFlow($dealId, $args);
            // If flow is changed reset data for the rest of the form
            if ($dataType) {
                $dealReset = $mapper->resetData($dataType);
                $dealReset->save();
            }
        }

        if (isset($args['expenses'])) {
            $getData = $mapper->mapFromEloquent();
            $typeDeal = $mapper->getTypeDealCalculate($getData);
            $calculate = $this->calculateTotalExpenses($args['expenses'], $getData, $typeDeal);
            $args['expenses']['totalExpenses'] = $calculate['total'];
            $args['expenses']['netOperatingIncome'] = $calculate['netOperatingIncome'];
            $args['expenses']['totalBusinessOperatingIncome'] = $calculate['totalBusinessOperatingIncome'];
            $getData['rent_roll']['totalIncome'] = $calculate['totalIncome'];
            unset($getData['lastStepStatus']);
            $deal = $mapper->mapToEloquent($getData, $user);
            $deal->save();
        }

        if (isset($args['owner_occupied'])) {
            $getData = $mapper->mapFromEloquent();
            if ($getData['expenses']['totalExpenses'] !== '') {
                $typeDeal = $mapper->getTypeDealCalculate($getData);
                $getData['expenses']['totalBusinessOperatingIncome'] = $this->calculateTotalExpensesOwnerOccupied($args['owner_occupied'], $getData, $typeDeal);
                unset($getData['lastStepStatus']);
                $deal = $mapper->mapToEloquent($getData, $user);
                $deal->save();
            }
        }

        $names = [];
        if (isset($args['sponsor'])) {
            $sponsorInfo = [];
            foreach ($args['sponsor']['sponsorInfo'] as $obj) {
                array_push($names, $obj['name']);
                $arr = ['assets_real_estate', 'assets_companies', 'assets_other', 'assets_liquid'];
                $sum = 0;
                $count = 0;
                foreach ($arr as $key) {
                    if ($obj[$key] !== '') {
                        $sum += (int) $obj[$key];
                        $count++;
                    }
                }

                $obj['net_worth'] = strval($sum - $obj['liabilities']);

                $obj['net_income'] = (float) $obj['annual_income'] - (float) $obj['annual_expenses'];

                if ($count >= 2) {
                    $obj['total_assets'] = $sum;
                } else {
                    $obj['total_assets'] = '';
                }

                $sponsorInfo[] = $obj;
            }
            $args['sponsor']['sponsorInfo'] = $sponsorInfo;
        }

        if (isset($args['rent_roll'])) {
            $getData = $mapper->mapFromEloquent();
            $typeDeal = $mapper->getTypeDealCalculate($getData);
            $tableArray = $args['rent_roll']['table'];
            $monthle_rent = 0;
            $annual_rent = 0;
            $annual_rent_sf = 0;
            foreach ($tableArray as $obj) {
                $monthle_rent += (float) $obj['monthle_rent'];
                $annual_rent += (float) $obj['annual_rent'];
                if (is_numeric($obj['annual_rent_sf'])) {
                    $annual_rent_sf += (float) $obj['annual_rent_sf'];
                }
            }
            $args['rent_roll']['monthle_total'] = strval($monthle_rent);
            $args['rent_roll']['annual_total'] = strval($annual_rent);
            $args['rent_roll']['annual_sf_total'] = strval($annual_rent_sf);
            if ($getData['expenses']['totalExpenses'] && $getData['expenses']['totalExpenses'] !== '') {
                $args['rent_roll']['totalIncome'] = $this->rentRollTotalIncome($args['rent_roll'], $getData, $typeDeal);
            } else {
                $args['rent_roll']['totalIncome'] = '';
            }
        }

        /**
         * Deal Mapper
         */
        $deal = $mapper->mapToEloquent($args, $user);
        $deal->sponsor_name = json_encode($names);
        $deal->save();

        if ($deal->finished) {
            if ($deal->currently_editing) {
                $deal->update([
                    'last_edited' => now(),
                    'currently_editing' => 0,
                ]);
            }

            $deal->update(['finished_at' => now()]);

            DealPublished::dispatch($deal);
        }

        /**
         * Mapped Output
         */
        return  $mapper->mapFromEloquentWith(null, [
            'finishApproved' => $userStatus == 'approved',
        ]);
    }

    private function rentRollTotalIncome($dealRentRoll, $dealData, $typeDeal)
    {
        $totalIncome = 0;
        $reimbursement = (float) str_replace(',', '', $dealData['expenses']['reimbursement']);

        if ($typeDeal === Deal::INVESTMENT_PURCHASE_REFINANCE) {
            if ($dealRentRoll['occupiedGroos'] === '') {
                $currentAnnualIncome = (float) str_replace(',', '', $dealRentRoll['annual_income']);
            } else {
                $currentAnnualIncome = (float) str_replace(',', '', $dealRentRoll['occupiedGroos']);
            }

            $otherIncome = 0;
            foreach ($dealRentRoll['other_income'] as $obj) {
                $otherIncome += (float) str_replace(',', '', $obj['amount']);
            }
            $totalIncome = $otherIncome + $currentAnnualIncome + $reimbursement;
        }

        return $totalIncome ? (string) $totalIncome : '';
    }

    /**
     * @param $dealExpenses
     * @param $dealData
     * @param $typeDeal
     * @return array
     */
    private function calculateTotalExpenses($dealExpenses, $dealData, $typeDeal): array
    {
        if (! $typeDeal) {
            return [
                'total' => '',
                'netOperatingIncome' => '',
                'totalBusinessOperatingIncome' => '',
                'totalIncome' => '',
            ];
        }

        $total = 0;
        $netOperatingIncome = 0;
        $totalBusinessOperatingIncome = 0;
        $totalIncome = 0;
        $reimbursement = 0;
        $someExp = 0;

        //Remove fields that are not expenses
        $nonExpenses = ['tax', 'triple', 'expDate', 'payroll', 'management', 'phaseStructure', 'additionalNotes', 'managementCompanyName', 'otherExpenses',
            'electricity', 'gas', 'commonArea', 'water', 'electricitySeparatelyMetered', 'gasSeparatelyMetered', 'waterSeparatelyMetered', 'reimbursement', ];
        foreach ($dealExpenses as $key => $dealExpens) {
            if ($key === 'reimbursement') {
                $reimbursement = (float) str_replace(',', '', $dealExpens);
            }
            if ($key === 'otherExpenses') {
                if ($dealExpens[0]['type'] !== '') {
                    foreach ($dealExpens as $exp) {
                        $total += (float) str_replace(',', '', $exp['amount']);
                    }
                }
            }
            if (in_array($key, $nonExpenses)) {
                continue;
            }
            $total += (float) str_replace(',', '', $dealExpens);
        }

        if ($typeDeal === Deal::INVESTMENT_PURCHASE_REFINANCE) {
            if ($dealData['rent_roll']['occupiedGroos'] === '') {
                $currentAnnualIncome = (float) str_replace(',', '', $dealData['rent_roll']['annual_income']);
            } else {
                $currentAnnualIncome = (float) str_replace(',', '', $dealData['rent_roll']['occupiedGroos']);
            }

            $otherIncome = 0;
            foreach ($dealData['rent_roll']['other_income'] as $obj) {
                $otherIncome += (float) str_replace(',', '', $obj['amount']);
            }

            $totalIncome = $otherIncome + $currentAnnualIncome + $reimbursement;
            $netOperatingIncome = $totalIncome - $total;
        }
        if ($typeDeal === Deal::OWNER_OCCUPIED_PURCHASE_REFINANCE) {
            $profit_amount = (float) str_replace(',', '', $dealData['owner_occupied']['profit_amount']);
            $totalBusinessOperatingIncome = $profit_amount - $total;
            $netOperatingIncome = '';
        }
        $total = $total === 0 ? '' : (string) $total;

        return [
            'total' => $total,
            'netOperatingIncome' => $netOperatingIncome ? (string) $netOperatingIncome : '',
            'totalBusinessOperatingIncome' => $totalBusinessOperatingIncome ? (string) $totalBusinessOperatingIncome : '',
            'totalIncome' => $totalIncome ? (string) $totalIncome : '',
        ];
    }

    private function calculateTotalExpensesOwnerOccupied($dealOwnerOccupied, $dealData, $typeDeal): string
    {
        if ($typeDeal === Deal::OWNER_OCCUPIED_PURCHASE_REFINANCE) {
            $total = (float) str_replace(',', '', $dealData['expenses']['totalExpenses']);
            $totalBusinessOperatingIncome = 0;
            $profit_amount = (float) str_replace(',', '', $dealOwnerOccupied['profit_amount']);
            $totalBusinessOperatingIncome = $profit_amount - $total;

            $total = $total === 0 ? '' : (string) $total;

            return $totalBusinessOperatingIncome ? (string) $totalBusinessOperatingIncome : '';
        }

        return '';
    }
}
