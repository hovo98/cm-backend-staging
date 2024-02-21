<?php

namespace App\DataTransferObjects;

use App\Lender;
use App\Quote;

class QuoteMapper extends JsonbMapper
{
    protected $type = Quote::class;

    public function mapFromQueryBuilder($obj = null): array
    {
        $activeObj = $obj ? $obj : $this->obj;

        return [
            'id' => $activeObj->id,
            'finished' => $activeObj->finished,
            'updated_at' => $activeObj->updated_at,
            'lastStepStatus' => $activeObj->lastStepStatus,
            'user_id' => $activeObj->user_id,
            'status' => $activeObj->status,
        ] + json_decode($activeObj->data, true);
    }

    public function dataUnit()
    {
        return [
            'id' => $this->obj->id,
            'dealID' => $this->obj->deal_id,
            'seen' => $this->obj->seen,
            'lastStepStatus' => $this->stringUnit(),
            'constructionLoans' => $this->QuoteConstructionLoansUnit(),
            'purchaseAndRefinanceLoans' => $this->QuotePurchaseAndRefinanceLoansUnit(),
            'message' => $this->stringUnit(),
            'lenderID' => $this->numberUnit(),
            'lenderFirstName' => $this->stringUnit(),
            'lenderLastName' => $this->stringUnit(),
            'lenderPhone' => $this->stringUnit(),
            'lenderEmail' => $this->stringUnit(),
            'lenderProfileImage' => $this->stringUnit(),
            'lenderBiography' => $this->stringUnit(),
            'lenderSpecialty' => $this->stringUnit(),
            'lenderLinkedin_url' => $this->stringUnit(),
        ];
    }

    public function mapFromEloquent($obj = null): array
    {
        $activeObj = $obj ?? $this->obj;

        $data = parent::mapFromEloquent($activeObj);

        $lender = $activeObj ? $activeObj->lender : Lender::find($data['user_id']);

        $lenderMeta = $lender->getProfileInfoLender();

        $data['dealID'] = $activeObj->deal_id;
        $data['seen'] = $activeObj->seen;
        $data['lenderID'] = $lender->id;
        $data['lenderFirstName'] = $lender->first_name;
        $data['lenderLastName'] = $lender->last_name;
        $data['lenderPhone'] = $lender->phone;
        $data['lenderEmail'] = $lender->email;
        $data['lenderProfileImage'] = $lender->profile_image;
        $data['lenderBiography'] = $lenderMeta['biography'];
        $data['lenderSpecialty'] = $lenderMeta['specialty'];
        $data['lenderLinkedin_url'] = $lenderMeta['linkedin_url'];

        return $data;
    }

    public function QuoteConstructionLoansUnit()
    {
        return [
            'requestedLoan' => $this->QuoteRequestedLoanUnit(),
            'landCosts' => $this->QuoteCostInputUnit(),
            'softCosts' => $this->QuoteCostInputUnit(),
            'hardCosts' => $this->QuoteCostInputUnit(),
            'lendTowardsCosts' => $this->QuoteCostInputUnit(),
            'interestRateType' => $this->enumUnit(),
            'interestRate' => $this->QuoteInterestRateUnit(),
            'constructionTerm' => $this->StringUnit(),
            'extensionOptionType' => $this->enumUnit(),
            'extensionOption' => $this->QuoteExtensionOptionUnit(),
            'recourseOptionType' => $this->enumUnit(),
            'recourseType' => $this->arrayUnit(),
            'recourseNote' => $this->stringUnit(),
            'collectingFeeType' => $this->enumUnit(),
            'collectingFee' => $this->QuoteFeeInputUnit(),
            'exitFeeType' => $this->enumUnit(),
            'exitFee' => $this->QuoteExitFeePercentUnit(),
            'permanentLoanOptionType' => $this->enumUnit(),
        ];
    }

    public function QuoteRequestedLoanUnit()
    {
        return [
            'dollarAmount' => $this->numberUnit(),
            'loanValue' => $this->stringUnit(),
            'loanCost' => $this->stringUnit(),
        ];
    }

    public function QuoteCostInputUnit()
    {
        return [
            'costAmount' => $this->numberUnit(),
            'costPercent' => $this->stringUnit(),
        ];
    }

    public function QuoteInterestRateUnit()
    {
        return [
            'fixedRateAmount' => $this->stringUnit(),
            'yieldText' => $this->stringUnit(),
            'spread' => $this->stringUnit(),
            'floor_rate' => $this->stringUnit(),
            'yield_second' => $this->stringUnit(),
            'spread_second' => $this->stringUnit(),
            'floor_rate_second' => $this->stringUnit(),
            'swap_value' => $this->stringUnit(),
        ];
    }

    public function QuoteExtensionOptionUnit()
    {
        return [
            'duration' => $this->stringUnit(),
            'feeAmount' => $this->numberUnit(),
            'feePercentage' => $this->stringUnit(),
            'allowed' => $this->numberUnit(),
        ];
    }

    public function QuoteFeeInputUnit()
    {
        return [
            'feePercent' => $this->stringUnit(),
            'feeAmount' => $this->numberUnit(),
        ];
    }

    public function QuoteExitFeePercentUnit()
    {
        return [
            'fee' => $this->QuoteFeeInputUnit(),
            'comment' => $this->stringUnit(),
        ];
    }

    public function QuotePurchaseAndRefinanceLoansUnit()
    {
        return [
            'offer' => $this->OfferAmountQuoteUnit(),
            'amountOfYears' => $this->numberUnit(),
            'amountOfYearsReset' => $this->enumUnit(),
            'interestRateType' => $this->enumUnit(),
            'interestRate' => $this->QuoteInterestRateUnit(),
            'interestPeriodType' => $this->enumUnit(),
            'interestPeriod' => $this->stringUnit(),
            'amortizationPeriod' => $this->stringUnit(),
            'recourseType' => $this->enumUnit(),
            'recourseOptions' => $this->arrayUnit(),
            'recourseNote' => $this->stringUnit(),
            'collectingOriginationFeeType' => $this->enumUnit(),
            'collectingOrigination' => $this->QuoteCostInputUnit(),
            'prePaymentYears' => $this->stringUnit(),
            'prePaymentCustomYear' => $this->arrayUnit(),
        ];
    }

    public function OfferAmountQuoteUnit()
    {
        return [
            'amount' => $this->numberUnit(),
            'loanToValueRatio' => $this->stringUnit(),
            'loanToCostRatio' => $this->stringUnit(),
        ];
    }

    public function QuoteInterestRateTypeUnit()
    {
        return [
            'amount' => $this->numberUnit(),
            'loanToValueRatio' => $this->stringUnit(),
            'loanToCostRatio' => $this->stringUnit(),
        ];
    }

    protected function compose_soft($args)
    {
        $data = $this->dataUnit();
        $existingData = $this->obj->data ?? [];
        foreach ($existingData as $key => $val) {
            $data[$key] = $val;
        }
        if (isset($args['message'])) {
            $data['message'] = $args['message'];
        }
        if (isset($args['constructionLoans'])) {
            foreach ($args['constructionLoans'] as $key => $val) {
                $data['constructionLoans'][$key] = $val;
            }
        }
        if (isset($args['purchaseAndRefinanceLoans'])) {
            foreach ($args['purchaseAndRefinanceLoans'] as $key => $val) {
                $data['purchaseAndRefinanceLoans'][$key] = $val;
            }
        }

        $this->obj->data = $data;
    }

    public function appendInducted($data)
    {
        $dollarAmount = 0;
        $interestRate = '';
        $origFee = 0;
        $rateTerm = 0;
        $interestRateSpread = '';
        $interestRateFloat = '';
        $interestSwap = false;
        $origFeePercent = '';

        if (isset($data['constructionLoans']['permanentLoanOptionType'])) {
            if ($data['constructionLoans']['permanentLoanOptionType'] === 2 || $data['constructionLoans']['permanentLoanOptionType'] === 1) {
                $dollarAmount = $data['constructionLoans']['requestedLoan']['dollarAmount'];
                $interestRate = $data['constructionLoans']['interestRate']['fixedRateAmount'];
                $interestRateSpread = $data['constructionLoans']['interestRate']['spread'];
                $origFee = $data['constructionLoans']['collectingFee']['feeAmount'];
                $origFeePercent = $data['constructionLoans']['collectingFee']['feePercent'];
                $rateTerm = (float) $data['constructionLoans']['constructionTerm'];
                $interestRateFloat = data_get($data, 'constructionLoans.interestRate.spread_second');
                $interestSwap = data_get($data, 'constructionLoans.interestRate.swap_value') !== '';
            }
            if ($data['constructionLoans']['permanentLoanOptionType'] === 0) {
                $dollarAmount = $data['purchaseAndRefinanceLoans']['offer']['amount'];
                $interestRate = $data['purchaseAndRefinanceLoans']['interestRate']['fixedRateAmount'];
                $interestRateSpread = $data['purchaseAndRefinanceLoans']['interestRate']['spread'];
                $origFee = $data['purchaseAndRefinanceLoans']['collectingOrigination']['costAmount'];
                $origFeePercent = $data['purchaseAndRefinanceLoans']['collectingOrigination']['costPercent'];
                $rateTerm = (float) $data['purchaseAndRefinanceLoans']['amountOfYears'];
                $interestRateFloat = $data['purchaseAndRefinanceLoans']['interestRate']['spread_second'];
                $interestSwap = $data['purchaseAndRefinanceLoans']['interestRate']['swap_value'] !== '';
            }
        }

        $i = [
            'dollar_amount' => $dollarAmount,
            'interest_rate' => $interestRate,
            'rate_term' => $rateTerm,
            'origFee' => $origFee,
            'interest_rate_spread' => $interestRateSpread,
            'interest_rate_float' => $interestRateFloat,
            'interest_swap' => $interestSwap,
            'origFeePercent' => $origFeePercent,
        ];
        $data['inducted'] = $i;

        return $data;
    }

    /**
     * @param $typeData
     * @return mixed
     */
    public function resetData($typeData)
    {
        if ($typeData !== 'permanentLoanOptionType') {
            return $this->obj;
        }

        $dataReset['purchaseAndRefinanceLoans'] = $this->QuotePurchaseAndRefinanceLoansUnit();

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
}
