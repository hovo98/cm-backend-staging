<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Deals;

use App\DataTransferObjects\DealMapper;
use App\Deal;
use App\Services\QueryServices\AbstractFilterDeals;
use App\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class FilterDeals
 *
 * @author  Boris Djemrovski <boris@forwardslashny.com>
 */
class FilterDealsLender extends AbstractFilterDeals
{
    /** @var FilterByAssetType */
    protected $filterByAssetTypeService;

    /** @var ForbiddenDeals */
    protected $forbiddenDealsService;

    /** @var ArchivedDeals */
    protected $archivedDealsService;

    /** @var IgnoredDeals */
    protected $ignoredDealsService;

    /** @var SavedDeals */
    protected $savedDealsService;

    /** @var PerfectCloseFit */
    protected $perfectCloseFitService;

    /** @var ?string */
    private $filterName;

    /**
     * FilterDeals constructor.
     *
     * @param  FilterByLoanSize  $filterByLoanSizeService
     * @param  FilterByAssetType  $filterByAssetTypeService
     * @param  FilterBySearchTerms  $filterBySearchTermsService
     * @param  SortByDeals  $sortByDealsService
     * @param  ForbiddenDeals  $forbiddenDealsService
     * @param  ArchivedDeals  $archivedDealsService
     * @param  IgnoredDeals  $ignoredDealsService
     * @param  SavedDeals  $savedDealsService
     * @param  PerfectCloseFit  $perfectCloseFitService
     */
    public function __construct(
        FilterByLoanSize $filterByLoanSizeService,
        FilterByAssetType $filterByAssetTypeService,
        FilterBySearchTerms $filterBySearchTermsService,
        SortByDeals $sortByDealsService,
        ForbiddenDeals $forbiddenDealsService,
        ArchivedDeals $archivedDealsService,
        IgnoredDeals $ignoredDealsService,
        SavedDeals $savedDealsService,
        PerfectCloseFit $perfectCloseFitService
    ) {
        parent::__construct($filterByLoanSizeService, $filterBySearchTermsService, $filterByAssetTypeService, $sortByDealsService);
        $this->forbiddenDealsService = $forbiddenDealsService;
        $this->archivedDealsService = $archivedDealsService;
        $this->ignoredDealsService = $ignoredDealsService;
        $this->savedDealsService = $savedDealsService;
        $this->perfectCloseFitService = $perfectCloseFitService;
    }

    /**
     * Deal filtration for Lenders
     *
     * **Mandatory (always applied) filters are:**
     * 1. Remove deals from colleague connections, except if colleague has ignored that deal
     * 2. Only finished deals
     *
     * **Contextual filter is:**
     * - User <-> Deal relation (ignored, archived or none)
     *
     * **Regular filters are:**
     * - Saved filter name (if perfect or close fit, the logic is specific because of location exclusions)
     *
     * *OR*
     * 1. Search terms
     * 2. Dollar amount
     * 3. Asset type
     *
     * @param  array  $args = [
     *                    'user' => 0, // User|int
     *                    'context' => 'general', // 'general', 'archived', 'ignored'
     *                    'filterName' => 'perfect_fit', // (string)
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
        $lender = $args['user'] instanceof User ? $args['user'] : User::find($args['user']);

        $this->parseArgs($args);

        $customFiltration = false;
        if ($this->loanSize || ($this->assetTypes && ! in_array(0, $this->assetTypes)) || $this->searchTerms || $this->sortBy) {
            $customFiltration = true;
        }

        $query = DB::table('deals')
            ->whereNull('deleted_at')
            ->where('finished', '=', true);

        //        $query = DB::table('deals')
        //            ->whereNull('deleted_at')
        //            ->where('finished', '=', true)
        //            ->where(function ($query) use ($lender) {
        //                $query->where('deals.quote_limit_reached', false)
        //                    ->orWhere(function ($query) use ($lender) {
        //                    $query->where('deals.quote_limit_reached', true)
        //                        ->whereIn('deals.id', function ($query) use ($lender) {
        //                        $query->select('deal_id')->from('quotes')
        //                            ->where('user_id', $lender->id)
        //                            ->where('quotes.finished', true);
        //                    });
        //                });
        //            });

        if ($customFiltration) {
            $query->select('deals.id');
        } else {
            $query->select('deals.*');
        }

        $query->join('deal_asset_type', 'deals.id', '=', 'deal_asset_type.deal_id');

        $query = $this->mandatoryFiltering($query);
        $query = $this->contextualFiltering($query);
        if ($this->context === 'general') {
            $query = $this->namedFiltering($query);
        }

        //If there no custom filters return perfect fit
        if ($customFiltration) {
            $dealsId = $query->get()->pluck('id')->toArray();

            $query = DB::table('deals')->select('deals.*')->whereIn('deals.id', $dealsId);
            $query->join('deal_asset_type', 'deals.id', '=', 'deal_asset_type.deal_id');
            $query = $this->customFiltering($query);
        }

        // Get available filters
        $availableDollarAmount['min'] = 0;
        $availableDollarAmount['max'] = 0;
        $availableAssetTypes = [];

        foreach ($query->cursor() as $deal) {
            $availableDollarAmount['min'] = ($availableDollarAmount['min'] < $deal->dollar_amount) && ($availableDollarAmount['min'] !== 0) ? $availableDollarAmount['min'] : $deal->dollar_amount;
            $availableDollarAmount['max'] = ($availableDollarAmount['max'] > $deal->dollar_amount) && ($deal->dollar_amount !== 0) ? $availableDollarAmount['max'] : $deal->dollar_amount;

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

        return [$this->paginate($query), $args['tags'], $availableDollarAmount, $availableAssetTypes, $this->user];
    }

    /**
     * Remove forbidden deals, get only finished deals
     *
     * @param  Builder  $query
     * @return Builder
     */
    private function mandatoryFiltering(Builder $query): Builder
    {
        $forbiddenDeals = $this->forbiddenDealsService->query(
            $this->user->id,
            $this->user->domain,
            true
        );

        $query->whereNotIn('deals.id', $forbiddenDeals);

        return $query;
    }

    /**
     * Gets ignored, archived, or none of it
     *
     * @param  Builder  $query
     * @return Builder
     */
    private function contextualFiltering(Builder $query): Builder
    {
        $ignoredDeals = $this->ignoredDealsService->query($this->user->id);
        $archivedDeals = $this->archivedDealsService->query($this->user->id);
        $savedDeals = $this->savedDealsService->query($this->user->id);

        switch ($this->context) {
            case 'ignored':
                $query->whereIn('deals.id', $ignoredDeals);
                break;

            case 'archived':
                $query->whereIn('deals.id', $archivedDeals);
                break;

            case 'saved':
                $query->whereIn('deals.id', $savedDeals);
                break;

            default:
                $query->whereNotIn('deals.id', $archivedDeals)
                      ->whereNotIn('deals.id', $ignoredDeals);
        }

        return $query;
    }

    /**
     * Logic to insert perfect/close fit filters into query
     *
     * @param  Builder  $query
     * @return Builder
     */
    private function namedFiltering(Builder $query): Builder
    {
        $query = $this->perfectCloseFitService->query($this->user->id, $this->filterName, $query);

        return $query;
    }

    /**
     * @param  array  $args
     */
    protected function parseArgs(array $args): void
    {
        parent::parseArgs($args);

        // Named filter
        $args = collect($this->defaultArgs)->merge($args);
        $this->filterName = $args->get('filterName');
    }
}
