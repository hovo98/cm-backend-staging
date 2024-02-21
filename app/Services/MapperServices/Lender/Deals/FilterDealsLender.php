<?php

declare(strict_types=1);

namespace App\Services\MapperServices\Lender\Deals;

use App\DataTransferObjects\DealMapper;
use App\DataTransferObjects\QuoteMapper;
use App\Deal;
use App\Interfaces\CollectionMapperService;
use App\Quote;
use App\User;

/**
 * Class FilterDealsLender
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class FilterDealsLender implements CollectionMapperService
{
    public function map($objs)
    {
        $mappedItems = [];
        foreach ($objs[0]['data'] as $deal) {
            $dealMapper = new DealMapper();
            $mappedDeal = $dealMapper->mapFromQueryBuilder($deal);
            $mappedDeal['loan_amount'] = $deal->dollar_amount ?? 0;
            $mappedDeal['type'] = $deal->main_type ?? 0;
            $checkIsSaved = $objs[4]->checkRelatedDeal($deal->id, Deal::SAVE_DEAL);
            $mappedDeal['show_address'] = $this->checkShowAddress($mappedDeal);
            $mappedDeal['is_saved'] = $checkIsSaved->isNotEmpty();
            $mappedDeal['quoted'] = $this->checkIfLenderQuotedDeal($deal->id, $objs[4]);
            $mappedDeal['quotes'] = $this->getQuotesForDeal($deal->id);
            $mappedItems[] = $mappedDeal;
        }
        $objs[0]['data'] = $mappedItems;
        $objs[0]['assetTypes'] = $objs[3];
        $objs[0]['dollarAmount'] = $objs[2];
        $objs[0]['tags'] = $objs[1];

        return $objs[0];
    }

    /**
     * @param $mappedDeal
     * @return bool
     */
    private function checkShowAddress($mappedDeal): bool
    {
        $loan_type = $mappedDeal['inducted']['loan_type'];

        if ($loan_type === 1) {
            return $mappedDeal['show_address_purchase'] === 'true' ? false : true;
        } elseif ($loan_type === 3) {
            return $mappedDeal['construction_loan']['show_address_construction'] === 'true' ? false : true;
        } else {
            return true;
        }
    }

    /**
     * @param $dealId
     * @param $user
     * @return bool
     */
    private function checkIfLenderQuotedDeal(int $dealId, User $user): bool
    {
        $dealEloquent = Deal::find($dealId);
        $lenderQuotes = $dealEloquent->quotes()->where('finished', true)->where('user_id', $user->id)->get();

        return $lenderQuotes->isNotEmpty();
    }

    private function getQuotesForDeal($id)
    {
        return Quote::select('quotes.*')
            ->leftJoin('users', 'quotes.user_id', '=', 'users.id')
            ->whereNull('users.deleted_at')
            ->whereNull('quotes.deleted_at')
            ->where('quotes.deal_id', $id)
            ->where('quotes.finished', true)
            ->get()
            ->map(function ($quote) {
                return (new QuoteMapper())->mapFromEloquent($quote);
            });
    }
}
