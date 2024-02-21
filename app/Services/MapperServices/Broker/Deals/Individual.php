<?php

namespace App\Services\MapperServices\Broker\Deals;

use App\DataTransferObjects\Broker\Deals\IndividualQuotes as QuoteMapper;
use App\Deal;
use App\Interfaces\CollectionMapperService;

class Individual implements CollectionMapperService
{
    public function map($objs, $deal = null): array
    {
        $arr = [];
        foreach ($objs as $lender) {
            if (! count($lender->quotes)) {
                continue;
            }
            $arr[] = [
                'lender' => $this->mappedLender($lender, $deal),
                'quotes' => $this->mappedQuotes($lender->quotes),
            ];
        }

        return $arr;
    }

    protected function mappedLender($lender, $dealId = null)
    {
        if ($dealId) {
            $deal = Deal::find($dealId);
        }

        $company = ($deal && $deal->isPremium()) ? $lender->getCompanyNameFromMetasOrFromCompanyRelationship() : "[Limited Deal - Upgrade to see This Detail]";

        return [
            'id' => $lender->id,
            'firstName' => $lender->first_name,
            'lastName' => $lender->last_name,
            'phone' => $lender->phone,
            'email' => $lender->email,
            'profileImage' => $lender->profile_image,
            'meta' => $lender->getProfileInfoLender(),
            'company' => $company,
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
}
