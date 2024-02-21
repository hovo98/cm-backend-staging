<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Deals;

use App\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class CheckForbiddenDeals
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class CheckForbiddenDeals
{
    /** @var array */
    protected $defaultArgs = [
        'user' => null,
    ];

    /** @var ForbiddenDeals */
    protected $forbiddenDealsService;

    /** @var IgnoredDeals */
    protected $ignoredDealsService;

    /** @var PerfectCloseFit */
    protected $perfectCloseFitService;

    /** @var User */
    protected $user;

    /**
     * FilterDeals constructor.
     *
     * @param  ForbiddenDeals  $forbiddenDealsService
     * @param  IgnoredDeals  $ignoredDealsService
     * @param  PerfectCloseFit  $perfectCloseFitService
     */
    public function __construct(
        ForbiddenDeals $forbiddenDealsService,
        IgnoredDeals $ignoredDealsService,
        PerfectCloseFit $perfectCloseFitService
    ) {
        $this->forbiddenDealsService = $forbiddenDealsService;
        $this->ignoredDealsService = $ignoredDealsService;
        $this->perfectCloseFitService = $perfectCloseFitService;
    }

    public function run(array $args): Builder
    {
        $this->parseArgs($args);

        $query = DB::table('deals')->whereNull('deleted_at')->where('finished', '=', true);
        $query->select('deals.id');
        $query->join('deal_asset_type', 'deals.id', '=', 'deal_asset_type.deal_id');

        $query = $this->mandatoryFiltering($query);
        $query = $this->removeIgnoredDeals($query);
        $query = $this->namedFiltering($query);

        return $query;
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
     * Logic to insert perfect fit filters into query
     *
     * @param  Builder  $query
     * @return Builder
     */
    private function namedFiltering(Builder $query): Builder
    {
        $query = $this->perfectCloseFitService->query($this->user->id, 'perfect_fit', $query);

        return $query;
    }

    /**
     * Gets ignored
     *
     * @param  Builder  $query
     * @return Builder
     */
    private function removeIgnoredDeals(Builder $query): Builder
    {
        $ignoredDeals = $this->ignoredDealsService->query($this->user->id);
        $query->whereNotIn('deals.id', $ignoredDeals);

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
    }
}
