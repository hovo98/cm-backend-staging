<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Quote;

use App\DataTransferObjects\QuoteMapper;
use App\Quote;

/**
 * Class IndividualAllData
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class IndividualAllData extends QuoteMapper
{
    protected $data;

    public function mapFromEloquent($obj = null): array
    {
        $this->data = parent::mapFromEloquent($this->obj);
        $allData = [
            'Dollar Amount' => $this->ifElse($this->data['constructionLoans']['requestedLoan']['dollarAmount'], 'dollar'),
            'Loan to value ratio' => $this->ifElse($this->data['constructionLoans']['requestedLoan']['loanValue'], 'percentSpecial'),
            'Loan to cost ratio' => $this->ifElse($this->data['constructionLoans']['requestedLoan']['loanCost'], 'percentSpecial'),
            'Land costs amount' => $this->ifElse($this->data['constructionLoans']['landCosts']['costAmount'], 'dollar'),
            'Land costs percent' => $this->ifElse($this->data['constructionLoans']['landCosts']['costPercent'], 'percent'),
            'Soft costs amount' => $this->ifElse($this->data['constructionLoans']['softCosts']['costAmount'], 'dollar'),
            'Soft costs percent' => $this->ifElse($this->data['constructionLoans']['softCosts']['costPercent'], 'percent'),
            'Hard costs amount' => $this->ifElse($this->data['constructionLoans']['hardCosts']['costAmount'], 'dollar'),
            'Hard costs percent' => $this->ifElse($this->data['constructionLoans']['hardCosts']['costPercent'], 'percent'),
            'Closing costs amount' => $this->ifElse($this->data['constructionLoans']['lendTowardsCosts']['costAmount'], 'dollar'),
            'Closing costs percent' => $this->ifElse($this->data['constructionLoans']['lendTowardsCosts']['costPercent'], 'percent'),
            'Interest rate ' => $this->checkEnum($this->data['constructionLoans']['interestRateType'], 'interest_rate'),
            'Fixed Rate Amount' => $this->ifElse($this->data['constructionLoans']['interestRate']['fixedRateAmount'], 'percent'),
            'Index' => $this->ifElse($this->data['constructionLoans']['interestRate']['yieldText'], ''),
            'Spread' => $this->ifElse($this->data['constructionLoans']['interestRate']['spread'], 'percent'),
            'Floor Rate' => $this->ifElse($this->data['constructionLoans']['interestRate']['floor_rate'], 'percent'),
            'Index ' => $this->ifElse($this->data['constructionLoans']['interestRate']['yield_second'], ''),
            'Spread ' => $this->ifElse($this->data['constructionLoans']['interestRate']['spread_second'], 'percent'),
            'Floor Rate ' => $this->ifElse($this->data['constructionLoans']['interestRate']['floor_rate_second'], 'percent'),
            'Swap' => $this->data['constructionLoans']['interestRate']['swap_value'],
            'Construction term' => $this->ifElse($this->data['constructionLoans']['constructionTerm'], ''),
            'Extension option' => $this->checkEnum($this->data['constructionLoans']['extensionOptionType'], 'decision_option'),
            'Duration' => $this->ifElse($this->data['constructionLoans']['extensionOption']['duration'], ''),
            'Fee amount' => $this->ifElse($this->data['constructionLoans']['extensionOption']['feeAmount'], 'dollar'),
            'Fee percent' => $this->ifElse($this->data['constructionLoans']['extensionOption']['feePercentage'], 'percent'),
            'Allowed' => $this->ifElse($this->data['constructionLoans']['extensionOption']['allowed'], ''),
            'Recourse Required' => $this->checkRecourse($this->checkEnum($this->data['constructionLoans']['recourseOptionType'], 'decision_option')),
            'Recourse type' => $this->getRecourseString($this->data['constructionLoans']['recourseType'], 'recourse_type'),
            'Recourse note ' => $this->data['constructionLoans']['recourseNote'] ?? '',
            'Collecting an origination fee ' => $this->checkEnum($this->data['constructionLoans']['collectingFeeType'], 'decision_option'),
            'Collecting fee amount' => $this->ifElse($this->data['constructionLoans']['collectingFee']['feeAmount'], 'dollar'),
            'Collecting fee percent' => $this->ifElse($this->data['constructionLoans']['collectingFee']['feePercent'], 'percent'),
            'Charging an exit fee' => $this->checkEnum($this->data['constructionLoans']['exitFeeType'], 'decision_option'),
            'Exit fee Percent' => $this->ifElse($this->data['constructionLoans']['exitFee']['fee']['feePercent'], 'percent'),
            'Exit fee amount' => $this->ifElse($this->data['constructionLoans']['exitFee']['fee']['feeAmount'], 'dollar'),
            'Comment' => $this->ifElse($this->data['constructionLoans']['exitFee']['comment'], ''),
            'Permanent loan option' => $this->checkEnum($this->data['constructionLoans']['permanentLoanOptionType'], 'decision_option'),
            'Offer dollar amount' => $this->ifElse($this->data['purchaseAndRefinanceLoans']['offer']['amount'], 'dollar'),
            'Loan To Cost Ratio ' => $this->ifElse($this->data['purchaseAndRefinanceLoans']['offer']['loanToCostRatio'], 'percentSpecial'),
            'Loan To Value Ratio ' => $this->ifElse($this->data['purchaseAndRefinanceLoans']['offer']['loanToValueRatio'], 'percentSpecial'),
            'How long would you fix your rate for' => ($this->data['purchaseAndRefinanceLoans']['amountOfYears'] && intval($this->data['purchaseAndRefinanceLoans']['amountOfYears']) >= 1) ? (intval($this->data['purchaseAndRefinanceLoans']['amountOfYears']) > 1 ? $this->data['purchaseAndRefinanceLoans']['amountOfYears'].' years' : $this->data['purchaseAndRefinanceLoans']['amountOfYears'].' year') : '',
            'Reset years' => $this->checkEnum($this->data['purchaseAndRefinanceLoans']['amountOfYearsReset'], 'decision_option'),
            'Interest rate' => $this->checkEnum($this->data['purchaseAndRefinanceLoans']['interestRateType'], 'interest_rate'),
            'Fixed Rate Amount ' => $this->ifElse($this->data['purchaseAndRefinanceLoans']['interestRate']['fixedRateAmount'], 'percent'),
            'Index  ' => $this->ifElse($this->data['purchaseAndRefinanceLoans']['interestRate']['yieldText'], ''),
            'Spread  ' => $this->ifElse($this->data['purchaseAndRefinanceLoans']['interestRate']['spread'], 'percent'),
            'Floor Rate  ' => $this->ifElse($this->data['purchaseAndRefinanceLoans']['interestRate']['floor_rate'], 'percent'),
            'Index   ' => $this->ifElse($this->data['purchaseAndRefinanceLoans']['interestRate']['yield_second'], ''),
            'Spread   ' => $this->ifElse($this->data['purchaseAndRefinanceLoans']['interestRate']['spread_second'], 'percent'),
            'Floor Rate   ' => $this->ifElse($this->data['purchaseAndRefinanceLoans']['interestRate']['floor_rate_second'], 'percent'),
            'Swap ' => $this->data['purchaseAndRefinanceLoans']['interestRate']['swap_value'],
            'Interest only period' => $this->checkEnum($this->data['purchaseAndRefinanceLoans']['interestPeriodType'], 'decision_option'),
            'Amount of time' => $this->ifElse($this->data['purchaseAndRefinanceLoans']['interestPeriod'], ''),
            'Amortization period (years)' => $this->ifElse($this->data['purchaseAndRefinanceLoans']['amortizationPeriod'], ''),
            'Recourse Required ' => $this->checkRecourse($this->checkEnum($this->data['purchaseAndRefinanceLoans']['recourseType'], 'decision_option')),
            'Recourse type ' => $this->getRecourseString($this->data['purchaseAndRefinanceLoans']['recourseOptions'], 'recourse_options'),
            'Recourse note' => $this->ifElse($this->data['purchaseAndRefinanceLoans']['recourseNote'], ''),
            'Collecting an origination fee' => $this->checkEnum($this->data['purchaseAndRefinanceLoans']['collectingOriginationFeeType'], 'decision_option'),
            'Cost amount' => $this->ifElse($this->data['purchaseAndRefinanceLoans']['collectingOrigination']['costAmount'], 'dollar'),
            'Cost Percent' => $this->ifElse($this->data['purchaseAndRefinanceLoans']['collectingOrigination']['costPercent'], 'percent'),
            'Pre-payment penalties' => $this->ifElse($this->data['purchaseAndRefinanceLoans']['prePaymentYears'], 'payment'),
            'Deleted at' => $this->obj->deleted_at ?? '',
            'Deleted by' => $this->ifIsDeletedOrEdit($this->obj->deleted_by),
        ];
        $customYear = $this->checkArrayData($this->data['purchaseAndRefinanceLoans']['prePaymentCustomYear']);

        if (! empty($customYear)) {
            $allData = array_merge($allData, $customYear);
        }

        return $allData;
    }

    public function ifIsDeletedOrEdit($val)
    {
        if ($val === 1) {
            return 'Edit';
        } elseif ($val === 2) {
            return 'Delete';
        }

        return '';
    }

    /**
     * @param $value
     * @param $flag
     * @return string|string[]
     */
    public function ifElse($value, $flag)
    {
        if ($value !== '' && $value !== '0' && $value !== 0) {
            switch ($flag) {
                case 'percent':
                    return $this->formatPercent($value);
                case 'percentSpecial':
                    return $this->formatPercentSpecial($value);
                case 'dollar':
                    return $this->formatDollarAmount($value);

                case 'payment':
                    if ($value === 'no') {
                        return 'No Prepayment Penalty';
                    }

                    return ucfirst(str_replace('-', ' ', $value));

                default:
                    return $value;
            }
        }

        return '';
    }

    /**
     * @param $value
     * @param $flag
     * @return string|string[]
     */
    private function checkEnum($value, $flag)
    {
        if ($flag === 'interest_rate') {
            $enumValue = Quote::INTEREST_RATE_TYPE[$value];
        }
        if ($flag === 'decision_option') {
            $enumValue = Quote::DECISION_OPTION_TYPE[$value];
        }
        if ($flag === 'recourse_type') {
            $enumValue = Quote::RECOURSE_TYPE[$value];
        }
        if ($flag === 'recourse_options') {
            $enumValue = Quote::RECOURSE_TYPE_PURCHASE[$value];
        }
        if ($flag === 'quote_status') {
            $enumValue = Quote::QUOTE_STATUS[$value];
        }

        return $this->formatEnum($enumValue);
    }

    /**
     * @param $value
     * @return string|string[]
     */
    public function formatEnum($value)
    {
        if ($value !== 'UNDEFINED') {
            $str = str_replace('_', ' ', $value);
            $str1 = str_replace('-', ' ', $str);

            return str_replace('\' ', '\'', ucwords(str_replace('\'', '\' ', strtolower($str1))));
        }

        return '';
    }

    /**
     * @param $field
     * @param $flag
     * @return string
     */
    private function getRecourseString($field, $flag): string
    {
        $recourse = '';
        if (empty($field)) {
            return $recourse;
        }
        foreach ($field as $key => $value) {
            if ($key === 1) {
                $recourse .= ', ';
            }
            $recourse .= $this->checkEnum($value, $flag);
        }

        return $recourse;
    }

    private function checkRecourse($value): string
    {
        if ($value === 'No') {
            return $value;
        }

        return '';
    }

    /**
     * @param  array  $customYear
     * @return array
     */
    private function checkArrayData($customYear = []): array
    {
        $dataMapped = [];
        $count = 1;
        if (empty($customYear)) {
            return $dataMapped;
        }
        foreach ($customYear as $amount) {
            if ($amount === '000.00' || $amount === '00.00' || $amount === '0.00') {
                $dataMapped[] = [
                    'Custom Pre-payment penalty '.$count => '0'.'%',
                ];
            } else {
                $dataMapped[] = [
                    'Custom Pre-payment penalty '.$count => $this->formatPercent($amount),
                ];
            }
            $count++;
        }

        return array_merge(...$dataMapped);
    }

    /**
     * @param $amount
     * @return string
     */
    public function formatPercentageCustom($amount): string
    {
        $amount = (string) $amount;

        return $amount.'%';
    }

    /**
     * @param $amount
     * @return string|string[]
     */
    public function formatDollarAmount($amount)
    {
        if ($amount !== 0 || $amount !== '0') {
            $fmt = new \NumberFormatter('en-US', \NumberFormatter::CURRENCY);
            $amountDollar = $fmt->formatCurrency((int) $amount, 'USD');
            $formatAmount = str_replace('.00', '', $amountDollar);
            if ($formatAmount === '$0') {
                return '';
            }

            return $formatAmount;
        }

        return '';
    }

    private function formatPercent($amount)
    {
        if (! $amount) {
            return '';
        }

        if ($amount === '000.00' || $amount === '00.00' || $amount === '0.00') {
            return '0%';
        }

        $zero = substr($amount, -1);
        $zeros = substr($amount, -2);
        if ($zeros === '00') {
            return substr($amount, 0, -3).'%';
        } elseif ($zeros === '.0') {
            return substr($amount, 0, -2).'%';
        }
        if ($zero === '0' || $zero === '.') {
            return substr($amount, 0, -1).'%';
        }

        return $amount.'%';
    }

    private function formatPercentSpecial($amount)
    {
        if (! $amount) {
            return '';
        }
        if ($amount === '000.00' || $amount === '00.00' || $amount === '0.00') {
            return '0%';
        }
        $length = strlen($amount);
        $zero = substr($amount, -1);
        $zeros = substr($amount, -2);
        if ($zeros === '00') {
            return substr($amount, 0, -3).'%';
        } elseif ($zeros === '.0') {
            return substr($amount, 0, -2).'%';
        }
        if ($zero === '.') {
            return substr($amount, 0, -1).'%';
        }
        if ($zero === '0' && $length === 5) {
            return substr($amount, 0, -1).'%';
        }

        return $amount.'%';
    }

    /**
     * @param $quoteId
     * @return array
     */
    public function getMappedDataExel($quoteId): array
    {
        $firstPartData = [
            $this->obj->id,
            $this->obj->deal_id,
            $this->obj->lenderWithTrashed->name(),
            $this->obj->lenderWithTrashed->getCompany()['company_name'] ?? '',
            $this->obj->lenderWithTrashed->email,
            $this->dateFinishedQuote($this->obj),
            $this->checkEnum($this->obj->status, 'quote_status'),
            $this->obj->unaccept_message,
        ];
        $mappedQuoteArray = $this->mapFromEloquent($quoteId);
        $array_values = array_values($mappedQuoteArray);

        return array_merge($firstPartData, $array_values);
    }

    /**
     * @param $quote
     * @return string
     */
    private function dateFinishedQuote($quote): string
    {
        if (! $quote || ! $quote->finished_at) {
            return '';
        }
        //Format time to PM or AM
        return date('m/d/Y, h:i A', strtotime(($quote->finished_at)->toString()));
    }

    /**
     * @return string[]
     */
    public function getHeadings(): array
    {
        return [
            'Quote ID',
            'Deal ID the quote is on',
            'Lender name',
            'Lender bank',
            'Lender email',
            'Date posted',
            'Status',
            'Undo accept reason',
            'Dollar Amount',
            'Loan to value ratio',
            'Loan to cost ratio',
            'Land costs amount',
            'Land costs percent',
            'Soft costs amount',
            'Soft costs percent',
            'Hard costs amount',
            'Hard costs percent',
            'Closing costs amount',
            'Closing costs percent',
            'Interest rate ',
            'Fixed Rate Amount',
            'Index',
            'Spread',
            'Floor Rate',
            'Index ',
            'Spread ',
            'Floor Rate ',
            'Swap',
            'Construction term',
            'Extension option',
            'Duration',
            'Fee amount',
            'Fee percent',
            'Allowed',
            'Recourse',
            'Recourse type',
            'Recourse note',
            'Collecting an origination fee ',
            'Collecting fee amount',
            'Collecting fee percent',
            'Charging an exit fee',
            'Exit fee Percent',
            'Exit fee amount',
            'Comment',
            'Permanent loan option',
            'Offer dollar amount',
            'Loan To Cost Ratio ',
            'Loan To Value Ratio ',
            'How long would you fix your rate for',
            'Reset years',
            'Interest rate',
            'Fixed Rate Amount ',
            'Index ',
            'Spread ',
            'Floor Rate ',
            'Index ',
            'Spread ',
            'Floor Rate ',
            'Swap',
            'Interest only period',
            'Amount of time',
            'Amortization period (years)',
            'Recourse ',
            'Recourse type ',
            'Recourse note ',
            'Collecting an origination fee',
            'Cost amount',
            'Cost Percent',
            'Pre-payment penalties',
            'Deleted at',
            'Deleted by',
            'Custom Pre-payment penalty',
        ];
    }
}
