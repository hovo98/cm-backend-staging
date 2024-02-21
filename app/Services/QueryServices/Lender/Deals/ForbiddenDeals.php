<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Deals;

use App\Services\QueryServices\AbstractQueryService;
use App\Services\QueryServices\Lender\Brokers\BrokersConnectedToMultipleLenders;
use App\Services\QueryServices\Lender\SameDomainLenders;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class LenderForbiddenDealsIds
 *
 * @author  Boris Djemrovski <boris@forwardslashny.com>
 */
class ForbiddenDeals extends AbstractQueryService
{
    /**
     * @var SameDomainLenders
     */
    private $sameDomainLenders;

    /**
     * @var BrokersConnectedToMultipleLenders
     */
    private $brokersConnectedToMultipleLenders;

    /**
     * @var IgnoredDealsMultipleLenders
     */
    private $ignoredDealsMultipleLenders;

    /**
     * @var CheckColleaguesPreferences
     */
    private $checkColleaguesPreferences;

    /**
     * @var ClosedDeals
     */
    private $closedDealsService;

    /**
     * LenderForbiddenDeals constructor.
     *
     * @param  SameDomainLenders  $sameDomainLenders
     * @param  BrokersConnectedToMultipleLenders  $brokersConnectedToMultipleLenders
     * @param  IgnoredDealsMultipleLenders  $ignoredDealsMultipleLenders
     * @param  CheckColleaguesPreferences  $checkColleaguesPreferences
     * @param  ClosedDeals  $closedDealsService
     */
    public function __construct(
        SameDomainLenders $sameDomainLenders,
        BrokersConnectedToMultipleLenders $brokersConnectedToMultipleLenders,
        IgnoredDealsMultipleLenders $ignoredDealsMultipleLenders,
        CheckColleaguesPreferences $checkColleaguesPreferences,
        ClosedDeals $closedDealsService
    ) {
        $this->sameDomainLenders = $sameDomainLenders;
        $this->brokersConnectedToMultipleLenders = $brokersConnectedToMultipleLenders;
        $this->ignoredDealsMultipleLenders = $ignoredDealsMultipleLenders;
        $this->checkColleaguesPreferences = $checkColleaguesPreferences;
        $this->closedDealsService = $closedDealsService;
    }

    /**
     * Returns the IDs of the forbidden deals for the Lender
     *
     * @param  array  $args id => int, domain => string
     * @return Collection
     */
    public function run(array $args): Collection
    {
        return $this->query($args['id'], $args['domain'])->get()->pluck('id');
    }

    /**
     * Returns full Builder query for the IDs of the forbidden deals for the Lender
     *
     * @param  int  $id
     * @param  string  $domain
     * @return Builder
     */
    public function query(int $id, string $domain, $flag = false): Builder
    {
        // Here they are querying for users with the same domain.
        $colleagues = $this->sameDomainLenders->query($id, $domain);

        // after getting the collegues they pull brokers that are connected with those collegues
        $brokersConnectedToColleagues = $this->brokersConnectedToMultipleLenders->query($colleagues);

        // they then get all the broker lenders (relationships to use as a reference)
        $checkIfExsist = DB::table('broker_lender')->select('id')->get()->pluck('id')->toArray();

        // When flag is False (this case) it checks all the "colleagues"
        // that are not individually linked to this broker (through broker_lender_pivot)
        // for each colleagues it returns an array of perfect fit deals
        $checkDeals = collect();
        if (count($checkIfExsist) !== 0) {
            $checkDeals = $this->checkColleaguesPreferences->query($colleagues->get()->pluck('id')->toArray(), $brokersConnectedToColleagues->get()->pluck('id')->toArray(), $flag);
        }

        // Get deal that were ignored by colleagues
        $dealsIgnoredByColleagues = $this->ignoredDealsMultipleLenders->query($colleagues);
        $checkClosedDeals = $this->closedDealsService->run(['id' => $id]);

        // Merge colleagues deals and closed deals from other lenders
        $forbiddenDealsIds = $checkDeals->merge($checkClosedDeals)->unique();

        return DB::table('deals')
                 ->select('id')
                 ->whereNotNull('user_id')
                 ->whereNull('deleted_at')
                 ->whereNotIn('id', $dealsIgnoredByColleagues)
                 ->whereIn('id', $forbiddenDealsIds);
    }
}
