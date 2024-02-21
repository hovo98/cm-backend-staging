<?php

declare(strict_types=1);

namespace App\Services\MapperServices\Broker\Quotes;

use App\DataTransferObjects\DealMapper;
use App\DataTransferObjects\Quote\Individual;
use App\DataTransferObjects\QuoteMapper;
use App\Interfaces\CollectionMapperService;
use Illuminate\Support\Facades\DB;

/**
 * Class AllQuotes
 */
class AllQuotes extends Individual implements CollectionMapperService
{
    public function map($paginator)
    {
        $mappedItems = [];
        foreach ($paginator[0]['data'] as $quote) {
            $dealMapper = new DealMapper();
            $dealObject = DB::table('deals')->where('id', $quote->deal_id)->get()->first();
            $mappedDeal = $dealMapper->mapFromQueryBuilder($dealObject);
            $quoteMapper = new QuoteMapper();
            $mappedQuote = $quoteMapper->mapFromQueryBuilder($quote);
            $mappedItems[] = [
                'quote_id' => $quote->id,
                'lender_id' => $quote->user_id,
                'dollarAmount' => $this->dollarAmount($mappedQuote),
                'deal' => [
                    'id' => $quote->deal_id,
                    'address' => $mappedDeal['location'],
                    'property_type' => data_get($mappedDeal, 'inducted.property_type.asset_types'),
                    'deal_type' => $dealObject->main_type,
                ],
                'interestRate' => $this->interestRate($mappedQuote),
                'rateTerm' => $this->rateTerm($mappedQuote),
                'origFee' => $this->origFee($mappedQuote),
            ];
        }

        $paginator[0]['data'] = $mappedItems;
        $paginator[0]['sponsorNames'] = $paginator[1];
        $paginator[0]['tags'] = $paginator[2];

        return $paginator[0];
    }
}
