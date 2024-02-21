<?php

namespace App\DataTransferObjects\Quote;

use App\DataTransferObjects\QuoteMapper;

class Individual extends QuoteMapper
{
    protected $data;

    public function mapFromEloquent($obj = null): array
    {
        $this->data = parent::mapFromEloquent($obj);

        return [
            'id' => $this->data['id'],
            'dollarAmount' => $this->dollarAmount(),
            'interestRate' => $this->interestRate(),
            'rateTerm' => $this->rateTerm(),
            'origFee' => $this->origFee(),
            'status' => $this->obj->status,
        ];
    }

    private function checkIfExtendedForm($quote)
    {
        return isset($quote['constructionLoans']['permanentLoanOptionType'])
            && (
                $quote['constructionLoans']['permanentLoanOptionType'] === 2
                || $quote['constructionLoans']['permanentLoanOptionType'] === 1
            );
    }

    public function dollarAmount($mappedQuote = false)
    {
        if ($mappedQuote) {
            $quote = $mappedQuote;
        } else {
            $quote = $this->data;
        }

        $constructionDollarAmount = $quote['constructionLoans']['requestedLoan']['dollarAmount'];
        $prDollarAmount = $quote['purchaseAndRefinanceLoans']['offer']['amount'];

        if ($constructionDollarAmount != 0) {
            return $this->formatDollarAmount($constructionDollarAmount);
        }
        if ($prDollarAmount != 0 && ! $this->checkIfExtendedForm($quote)) {
            return $this->formatDollarAmount($prDollarAmount);
        }

        return '';
    }

    public function interestRate($mappedQuote = false)
    {
        if ($mappedQuote) {
            $quote = $mappedQuote;
        } else {
            $quote = $this->data;
        }

        if ($quote['constructionLoans']['interestRate']['fixedRateAmount'] !== '') {
            return $this->formatPercentage($quote['constructionLoans']['interestRate']['fixedRateAmount']);
        } elseif ($quote['purchaseAndRefinanceLoans']['interestRate']['fixedRateAmount'] && ! $this->checkIfExtendedForm($quote)) {
            return $this->formatPercentage($quote['purchaseAndRefinanceLoans']['interestRate']['fixedRateAmount']);
        } elseif ($quote['constructionLoans']['interestRate']['yieldText'] !== '') {
            return $quote['constructionLoans']['interestRate']['yieldText'].' + '.$this->formatPercentage($quote['constructionLoans']['interestRate']['spread']);
        } elseif ($quote['constructionLoans']['interestRate']['yield_second'] !== '') {
            return $quote['constructionLoans']['interestRate']['yield_second'].' + '.$this->formatPercentage($quote['constructionLoans']['interestRate']['spread_second']);
        } elseif ($quote['constructionLoans']['interestRate']['swap_value'] !== '') {
            return 'Swap';
        } elseif (! $this->checkIfExtendedForm($quote)) {
            if ($quote['purchaseAndRefinanceLoans']['interestRate']['yieldText'] !== '') {
                return $quote['purchaseAndRefinanceLoans']['interestRate']['yieldText'].' + '.$this->formatPercentage($quote['purchaseAndRefinanceLoans']['interestRate']['spread']);
            } elseif ($quote['purchaseAndRefinanceLoans']['interestRate']['swap_value'] !== '') {
                return 'Swap';
            }

            return $quote['purchaseAndRefinanceLoans']['interestRate']['yield_second'].' + '.$this->formatPercentage($quote['purchaseAndRefinanceLoans']['interestRate']['spread_second']);
        } else {
            return '';
        }
    }

    public function rateTerm($mappedQuote = false)
    {
        if ($mappedQuote) {
            $quote = $mappedQuote;
        } else {
            $quote = $this->data;
        }
        $constructionTerm = $quote['constructionLoans']['constructionTerm'];
        if ($constructionTerm != '') {
            return $constructionTerm;
        } else {
            return $quote['purchaseAndRefinanceLoans']['amountOfYears'];
        }
    }

    public function origFee($mappedQuote = false)
    {
        if ($mappedQuote) {
            $quote = $mappedQuote;
        } else {
            $quote = $this->data;
        }

        $constructionCostAmount = $quote['constructionLoans']['collectingFee']['feeAmount'];
        $constructionCostPercent = $quote['constructionLoans']['collectingFee']['feePercent'];
        $prCostAmount = $quote['purchaseAndRefinanceLoans']['collectingOrigination']['costAmount'];
        $prCostPercent = $quote['purchaseAndRefinanceLoans']['collectingOrigination']['costPercent'];

        if ($constructionCostAmount != 0) {
            return $this->formatDollarAmount($constructionCostAmount);
        }
        if ($constructionCostPercent !== '') {
            return $this->formatPercentage($constructionCostPercent);
        }

        if ($prCostAmount != 0 && ! $this->checkIfExtendedForm($quote)) {
            return $this->formatDollarAmount($prCostAmount);
        }

        if ($prCostPercent !== '' && ! $this->checkIfExtendedForm($quote)) {
            return $this->formatPercentage($prCostPercent);
        }

        return '';
    }

    public function formatDollarAmount($amount)
    {
        $fmt = new \NumberFormatter('en-US', \NumberFormatter::CURRENCY);
        $formatDollar = $fmt->formatCurrency($amount, 'USD');
        $formatAmount = str_replace('.00', '', $formatDollar);

        return $formatAmount;
    }

    private function formatPercentage($amount)
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
}
