<?php

declare(strict_types=1);

namespace App\Services\QueryServices;

use App\Services\QueryServices\Lender\Deals\FilterByAssetType;
use App\Services\QueryServices\Lender\Deals\FilterByLoanSize;
use App\Services\QueryServices\Lender\Deals\FilterBySearchTerms;
use App\Services\QueryServices\Lender\Deals\SortByDeals;
use App\User;
use Illuminate\Database\Query\Builder;

/**
 * Class AbstractFilterDeals
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
abstract class AbstractFilterDeals extends AbstractQueryService
{
    /** @var array */
    protected $defaultArgs = [
        'user' => null,
        'context' => 'general',
        'filterName' => null,
        'searchTerms' => null,
        'loanSize' => null,
        'assetTypes' => null,
        'sortBy' => null,
        'currentPage' => 1,
        'perPage' => 10,
        'tags' => null,
    ];

    /** @var FilterByLoanSize */
    protected $filterByLoanSizeService;

    /** @var FilterByAssetType */
    protected $filterByAssetTypeService;

    /** @var FilterBySearchTerms */
    protected $filterBySearchTermsService;

    /** @var SortByDeals */
    protected $sortByDealsService;

    /** @var ?User */
    protected $user;

    /** @var ?string */
    protected $searchTerms;

    /** @var ?array */
    protected $loanSize;

    /** @var ?int[] */
    protected $assetTypes;

    /** @var ?string */
    protected $context;

    /** @var ?array */
    protected $sortBy;

    /** @var ?array */
    protected $tags;

    /**
     * AbstractFilterDeals constructor.
     *
     * @param  FilterByLoanSize  $filterByLoanSizeService
     * @param  FilterBySearchTerms  $filterBySearchTermsService
     * @param  FilterByAssetType  $filterByAssetTypeService
     * @param  SortByDeals  $sortByDealsService
     */
    public function __construct(
        FilterByLoanSize $filterByLoanSizeService,
        FilterBySearchTerms $filterBySearchTermsService,
        FilterByAssetType $filterByAssetTypeService,
        SortByDeals $sortByDealsService
    ) {
        $this->filterByLoanSizeService = $filterByLoanSizeService;
        $this->filterBySearchTermsService = $filterBySearchTermsService;
        $this->filterByAssetTypeService = $filterByAssetTypeService;
        $this->sortByDealsService = $sortByDealsService;
    }

    /**
     * Handling the filtering by user input
     *
     * @param  Builder  $query
     * @return Builder
     */
    protected function customFiltering(Builder $query): Builder
    {
        if ($this->loanSize) {
            $query = $this->filterByLoanSizeService->query($this->loanSize['min'], $this->loanSize['max'], $query);
        }

        if ($this->assetTypes && ! in_array(0, $this->assetTypes)) {
            $query = $this->filterByAssetTypeService->query($this->assetTypes, $min_amount = 0, $max_amount = 0, $query);
        }

        if ($this->searchTerms) {
            $query = $this->filterBySearchTermsService->query($this->searchTerms, $query);
        }

        if ($this->sortBy) {
            $query = $this->sortByDealsService->query($this->sortBy, $query);
        }

        return $query;
    }

    /**
     * Parse input args with the defaults
     *
     * @param  array  $args
     */
    protected function parseArgs(array $args): void
    {
        // Merge with defaults
        $args = collect($this->defaultArgs)->merge($args);

        // Get the user (lender)
        if (is_numeric($args->get('user'))) {
            $this->user = User::find($args->get('user'));
        } elseif ($args->get('user') instanceof User) {
            $this->user = $args->get('user');
        } else {
            $this->user = auth()->user();
        }

        // Context
        $this->context = $args->get('context');

        // Sort by parameter
        $this->sortBy = $args->get('sortBy');

        // User inputs
        $this->searchTerms = $args->get('searchTerms');
        $this->loanSize = $args->get('loanSize');
        $this->assetTypes = $args->get('assetTypes');
        $this->tags = $args->get('tags');

        // Pagination
        $this->setCurrentPage($args->get('currentPage'));
        $this->setPerPage($args->get('perPage'));
    }
}
