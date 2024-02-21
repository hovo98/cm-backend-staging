<?php

namespace App\Services\MapperServices\Lender\Quote;

use App\DataTransferObjects\QuoteMapper;
use App\Deal;
use App\Interfaces\IndividualMapperService;
use App\Lender;
use App\User;

class Individual implements IndividualMapperService
{
    // Create mockup object for easier CS development
    public function map($obj)
    {
        $quoteMapper = new QuoteMapper($obj->id);
        $mappedQuote = $quoteMapper->mapFromEloquent();
        $companyName = $this->getBrokerCompanyName($obj->deal_id);

        return [
            'quote' => $mappedQuote,
            'broker' => $companyName,
        ];
    }

    protected function mappedLender(Lender $lender)
    {
        return [
            'id' => $lender->id,
            'firstName' => $lender->first_name,
            'lastName' => $lender->last_name,
            'phone' => $lender->phone,
            'email' => $lender->email,
            'profileImage' => $lender->profile_image,
            'meta' => $lender->getProfileInfoLender(),
            // 'companyName' => $this->getLenderCompanyName($lender->id),
        ];
    }

    protected function mappedQuotes($quotes)
    {
        $mappedQuotes = [];
        foreach ($quotes as $quote) {
            $quoteMapper = new QuoteMapper($quote->id);
            $mappedQuote = $quoteMapper->mapFromEloquent();
            $mappedQuotes[] = $mappedQuote;
        }

        return $mappedQuotes;
    }

    private function getBrokerCompanyName($dealId)
    {
        $deal = Deal::find($dealId);
        $userBroker = User::find($deal->user_id);

        return  [
            'companyName' =>  $userBroker->getCompanyNameFromMetasOrFromCompanyRelationship(),
        ];
    }
}
