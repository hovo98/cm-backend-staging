<?php

namespace App\Services\MapperServices\Lender\Deals;

use App\Interfaces\IndividualMapperService;

class DealForQuoteCreateForm implements IndividualMapperService
{
    public function map($obj)
    {
        $inducted = $obj->data['inducted'];

        if ($inducted['loan_type'] == 1) {
            $loanType = 'PURCHASE';
        }

        if ($inducted['loan_type'] == 2) {
            $loanType = 'REFINANCE';
        }

        if ($inducted['loan_type'] == 3) {
            $loanType = 'CONSTRUCTION';
        }

        if ($inducted['property_type']['type'] == 1) {
            $propertyType = 'INVESTMENT';
        }

        if ($inducted['property_type']['type'] == 2) {
            $propertyType = 'OWNER_OCCUPIED';
        }

        if ($inducted['property_type']['type'] == 3) {
            $propertyType = 'CONSTRUCTION';
        }

        return [
            'property_type' => $propertyType,
            'loan_type' => $loanType,
            'loan_amount' => $inducted['loan_amount'],
            'land_cost' => $obj->data['construction']['land_cost'],
            'hard_cost' => $obj->data['construction']['hard_cost'],
            'soft_cost' => $obj->data['construction']['soft_cost'],
        ];
    }
}
