<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Broker\Quotes;

use App\Deal;
use App\Services\QueryServices\AbstractQueryService;
use App\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class AllQuotes
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class AllQuotes extends AbstractQueryService
{
    /** @var array */
    protected $defaultArgs = [
        'broker' => null,
        'searchLocation' => null,
        'sponsorNames' => null,
        'sortBy' => null,
        'tags' => null,
        'currentPage' => 1,
        'perPage' => 10,
    ];

    /** @var FilterBySearchLocation */
    private $filterBySearchLocationService;

    /** @var FilterBySponsorName */
    private $filterBySponsorNameService;

    /** @var SortBy */
    private $filterSortByDealsService;

    /* @var User */
    protected $broker;

    /** @var ?string */
    private $searchLocation;

    /** @var ?array */
    private $sponsorNames;

    /** @var string */
    private $sponsorName;

    /** @var ?array */
    private $tags;

    /** @var ?array */
    private $sortBy;

    /**
     * AllQuotes constructor.
     *
     * @param  FilterBySearchLocation  $filterBySearchLocationService
     * @param  FilterBySponsorName  $filterBySponsorNameService
     * @param  SortBy  $filterSortByDealsService
     */
    public function __construct(
        FilterBySearchLocation $filterBySearchLocationService,
        FilterBySponsorName $filterBySponsorNameService,
        SortBy $filterSortByDealsService
    ) {
        $this->filterBySearchLocationService = $filterBySearchLocationService;
        $this->filterBySponsorNameService = $filterBySponsorNameService;
        $this->filterSortByDealsService = $filterSortByDealsService;
    }

    public function run(array $args)
    {
        $this->parseArgs($args);
        //Mandatory get only brokers deals
        $dealIdsQuery = DB::table('deals')->select('deals.id')
                                                ->whereNull('deals.deleted_at')
                                                ->where('deals.user_id', '=', $this->broker->id)
                                                ->where('deals.finished', true);

        //Custom filtering deals
        $dealIdsQuery = $this->customFiltering($dealIdsQuery);

        //Return sponsor names from all deals from broker
        $filterSponsors = [];

        Deal::withCount([
            'quotes as quoteCount' => function ($query) {
                $query->where('finished', true)->whereNull('deleted_at');
            }
        ])
            ->whereIn('id', $dealIdsQuery->pluck('id')->all())
            ->each(function ($deal) use (&$filterSponsors) {
                $hasQuotes =  $deal->quoteCount;
                if ($deal->sponsor_name && $hasQuotes && ! in_array($deal->sponsor_name, $filterSponsors)) {
                    $filterSponsors[] = ucwords($deal->sponsor_name);
                }
            });
        $filterSponsorsUnique = array_unique($filterSponsors);

        //Get quotes from a deal
        $quotes = DB::table('quotes')->whereNull('quotes.deleted_at')
                                            ->where('quotes.finished', true)
                                            ->whereIn('quotes.deal_id', $dealIdsQuery);

        //Sort quotes by deals and quotes fields
        if ($this->sortBy) {
            $quotes = $this->filterSortByDealsService->query($this->sortBy, $quotes);
        }

        return [$this->paginate($quotes), $filterSponsorsUnique, $args['tags']];
    }

    /**
     * Filtering by location and sponsor names
     *
     * @param  Builder  $query
     * @return Builder
     */
    private function customFiltering(Builder $query): Builder
    {
        if ($this->searchLocation) {
            $query = $this->filterBySearchLocationService->query($this->searchLocation, $query);
        }

        if ($this->sponsorNames && ! in_array('', $this->sponsorNames)) {
            $query = $this->filterBySponsorNameService->query($this->sponsorNames, '', $query);
        } elseif ($this->sponsorName) {
            $query = $this->filterBySponsorNameService->query([], $this->sponsorName, $query);
        }

        return $query;
    }

    /**
     * Parse input args with the defaults
     *
     * @param  array  $args
     */
    private function parseArgs(array $args): void
    {
        // Merge with defaults
        $args = collect($this->defaultArgs)->merge($args);

        // Get the user (lender)
        if (is_numeric($args->get('broker'))) {
            $this->broker = User::find($args->get('broker'));
        } elseif ($args->get('broker') instanceof User) {
            $this->broker = $args->get('broker');
        } else {
            $this->broker = auth()->user();
        }

        // User inputs
        $this->searchLocation = $args->get('searchLocation');
        $this->sponsorNames = $args->get('sponsorNames');
        $this->sponsorName = $args->get('sponsorName');
        $this->sortBy = $args->get('sortBy');
        $this->tags = $args->get('tags');

        // Pagination
        $this->setCurrentPage($args->get('currentPage'));
        $this->setPerPage($args->get('perPage'));
    }
}
