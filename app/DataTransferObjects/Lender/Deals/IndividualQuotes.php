<?php

namespace App\DataTransferObjects\Lender\Deals;

use App\DataTransferObjects\Quote\Individual;
use App\User;

class IndividualQuotes extends Individual
{
    public function mapFromEloquent($obj = null): array
    {
        parent::mapFromEloquent($obj);

        return [
            'id' => $this->data['id'],
            'dollarAmount' => $this->dollarAmount(),
            'interestRate' => $this->interestRate(),
            'rateTerm' => $this->rateTerm(),
            'origFee' => $this->origFee(),
            'company' => $this->company(),
            'status' => $this->obj->status,
        ];
    }

    public function company(): string
    {
        $lender = User::find($this->data['user_id']);
        $company = $lender->getCompany();
        if (isset($company['company_name'])) {
            return $company['company_name'];
        }

        return '';
    }
}
