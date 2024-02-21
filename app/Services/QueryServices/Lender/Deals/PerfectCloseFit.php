<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Deals;

use App\Lender;
use App\Services\QueryServices\AbstractQueryService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Class PerfectCloseFit
 *
 * @author  Boris Djemrovski <boris@forwardslashny.com>
 */
class PerfectCloseFit extends AbstractQueryService
{
    /**
     * @var FilterByLoanSize
     */
    private $filterByLoanSize;

    /**
     * @var FilterLocationPerfectCloseFit
     */
    private $filterLocationPerfectCloseFit;

    /**
     * @var FilterByAssetType
     */
    private $filterByAssetTypeService;

    /**
     * @var FilterByTypeOfLoan
     */
    private $filterByTypeOfLoanService;

    /**
     * @param  FilterByLoanSize  $filterByLoanSize
     * @param  FilterLocationPerfectCloseFit  $filterLocationPerfectCloseFit
     * @param  FilterByAssetType  $filterByAssetTypeService
     * @param  FilterByTypeOfLoan  $filterByTypeOfLoanService
     */
    public function __construct(
        FilterByLoanSize $filterByLoanSize,
        FilterLocationPerfectCloseFit $filterLocationPerfectCloseFit,
        FilterByAssetType $filterByAssetTypeService,
        FilterByTypeOfLoan $filterByTypeOfLoanService
    ) {
        $this->filterByLoanSize = $filterByLoanSize;
        $this->filterLocationPerfectCloseFit = $filterLocationPerfectCloseFit;
        $this->filterByAssetTypeService = $filterByAssetTypeService;
        $this->filterByTypeOfLoanService = $filterByTypeOfLoanService;
    }

    /**
     * Returns the Deal IDs that are ignored or archived by the Lender
     *
     * @param  array  $args
     * @return Collection
     */
    public function run(array $args): Collection
    {
        return $this->query($args['lenderId'], $args['type'], $args['query'], $args['matchedBrokers'])->get()->pluck('id');
    }

    /**
     * Returns raw query for the Deal IDs that are ignored or archived by the Lender
     *
     * @param  int  $lenderId
     * @param  string  $type
     * @param  Builder  $query
     * @param  array  $matchedBrokers
     * @return Builder
     */
    public function query(int $lenderId, string $type, Builder $query, array $matchedBrokers = []): Builder
    {
        $lender = Lender::find($lenderId);
        $typeFit = $type ?? '';

        if ($typeFit === 'close_fit') {
            $dealPreferences = $lender->getCloseFit();
        } else {
            $dealPreferences = $lender->getPerfectFit();
        }

        if (! $dealPreferences) {
            return $query;
        }
        if (! empty($matchedBrokers)) {
            $query->whereIn('user_id', $matchedBrokers);
        }

        $workingAreas = $dealPreferences->getWorkingAreas();
        $excludedArea = $dealPreferences->getExcludedAreas();

        $loanSize = $dealPreferences->getLoanSize() ?? [];
        $assetType = $dealPreferences->getAssetTypes();
        $multyfamilyValues = $dealPreferences->getMultifamily();
        $multiMin = $multyfamilyValues['min_amount'] ?? 0;
        $multiMax = $multyfamilyValues['max_amount'] ?? 0;
        $typeOfLoans = $dealPreferences->getTypeOfLoansLender() ?? [];

        $query = $this->filterByLoanSize->query($loanSize['min'], $loanSize['max'], $query);
        $query = $this->filterLocationPerfectCloseFit->query($workingAreas, $excludedArea, $query);
        $query = $this->filterByAssetTypeService->query($assetType, $multiMin, $multiMax, $query);
        $query = $this->filterByTypeOfLoanService->query($typeOfLoans, $query);

        return $query;
    }
}
