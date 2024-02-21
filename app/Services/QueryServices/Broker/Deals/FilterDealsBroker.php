<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Broker\Deals;

use App\DataTransferObjects\DealMapper;
use App\Deal;
use App\Services\QueryServices\AbstractFilterDeals;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class FilterDealsBroker
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class FilterDealsBroker extends AbstractFilterDeals
{
    /**
     * Deal filtration for Brokers
     *
     * **Mandatory (always applied) filters are:**
     * 1. Returns only his deals
     *
     * **Contextual filter is:**
     * Published or Drafts Deals
     *
     * Custom filters
     * 1. Search terms
     * 2. Dollar amount
     * 3. Asset type
     *
     * @param  array  $args = [
     *                    'user' => 0, // User|int
     *                    'context' => 'general', // 'drafts'
     *                    'search' => ['term 1', 'term 2'],
     *                    'loanSize' => ['min' => 0, 'max' => 0],
     *                    'assetTypes' => [1, 4, 7],
     *                    'currentPage' => 1,
     *                    'perPage' => 10,
     *                   ]
     * @return array
     */
    public function run(array $args): array
    {
        $this->parseArgs($args);

        $query = DB::table('deals');

        // Deals with the unseen quotes go first in any case.
        $query->orderByDesc('unseen_quotes');

        if ($args['sortBy']['sort'] === 'property_type' || ! in_array(0, $args['assetTypes'])) {
            $query->join('deal_asset_type', 'deals.id', '=', 'deal_asset_type.deal_id');
        }

        if ($this->context === 'draft') {
            $this->loanSize = null;
            $this->sortBy = ['sort' => 'updated_at', 'by' => 'desc'];
        }

        $query = $this->mandatoryFiltering($query);
        $query = $this->contextualFiltering($query);
        $query = $this->customFiltering($query);

        if ($args['sortBy']['sort'] === 'property_type' || ! in_array(0, $args['assetTypes'])) {
            $query->select('deals.*');
        }

        // Get available filters
        $availableDollarAmount['min'] = 0;
        $availableDollarAmount['max'] = 0;
        $availableAssetTypes = [];

        foreach ($query->cursor() as $deal) {
            $availableDollarAmount['min'] = ($availableDollarAmount['min'] < $deal->dollar_amount) && ($availableDollarAmount['min'] !== 0) ? $availableDollarAmount['min'] : $deal->dollar_amount;
            $availableDollarAmount['max'] = $availableDollarAmount['max'] > $deal->dollar_amount ? $availableDollarAmount['max'] : $deal->dollar_amount;

            $dealMapper = new DealMapper();
            $mappedDeal = $dealMapper->mapFromQueryBuilder($deal);
            $dealAssetTypes = $mappedDeal['inducted']['property_type']['asset_types'];
            if ($deal->main_type && ! in_array($deal->main_type, $availableAssetTypes)) {
                $availableAssetTypes[] = $deal->main_type;
            }
            if (in_array(Deal::CONSTRUCTION, $dealAssetTypes) && ! in_array(Deal::CONSTRUCTION, $availableAssetTypes)) {
                $availableAssetTypes[] = Deal::CONSTRUCTION;
            }
        }

        return [$this->paginate($query), $args['tags'], $availableDollarAmount, $availableAssetTypes];
    }

    /**
     * Returns only Broker's deals
     *
     * @param  Builder  $query
     * @return Builder
     */
    private function mandatoryFiltering(Builder $query): Builder
    {
        $query->where('deals.user_id', '=', $this->user->id)
              ->whereNull('deals.deleted_at');

        return $query;
    }

    /**
     * Gets draft and published deals from that broker
     *
     * @param  Builder  $query
     * @return Builder
     */
    private function contextualFiltering(Builder $query): Builder
    {
        if ($this->context === 'draft') {
            $query->where('deals.finished', '=', false);
        } else {
            $query->where('deals.finished', '=', true);
        }

        return $query;
    }
}
