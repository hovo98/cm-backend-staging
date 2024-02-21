<?php

declare(strict_types=1);

namespace App\Services\MapperServices\Broker\Deals;

use App\DataTransferObjects\DealMapper;
use App\Deal;
use App\Interfaces\CollectionMapperService;
use App\Quote;

/**
 * Class FilterDealsBroker
 */
class FilterDealsBroker implements CollectionMapperService
{
    public function map($objs)
    {
        $mappedItems = [];

        $dealIds = collect($objs[0]['data'])->pluck('id')->toArray();
        $quotes = Quote::whereIn('deal_id', $dealIds)->where('finished', true)->get();

        foreach ($objs[0]['data'] as $deal) {
            //Count Quotes for Deal
            $queryQuotes = $quotes->where('deal_id', $deal->id);
            $dealMapper = new DealMapper();
            $mappedDeal = $dealMapper->mapFromQueryBuilder($deal);

            //Inject number of Quotes for Deal
            $mappedDeal['total_quotes'] = $queryQuotes->count() ?? 0;
            $mappedDeal['has_new_quotes'] = $deal->unseen_quotes;
            $mappedDeal['loan_amount'] = $deal->dollar_amount ?? 0;
            $mappedDeal['type'] = $deal->main_type ?? 0;
            $mappedDeal['show_address'] = true;
            $mappedDeal['is_premium'] = Deal::find($deal->id)?->isPremium();
            $mappedDeal['deal_type'] = Deal::find($deal->id)?->getDealType();
            $mappedItems[] = $mappedDeal;
        }

        $objs[0]['data'] = $mappedItems;
        $objs[0]['assetTypes'] = $objs[3];
        $objs[0]['dollarAmount'] = $objs[2];
        $objs[0]['tags'] = $objs[1];

        return $objs[0];
    }
}
