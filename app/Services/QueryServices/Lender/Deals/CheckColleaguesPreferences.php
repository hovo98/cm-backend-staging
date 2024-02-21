<?php

declare(strict_types=1);

namespace App\Services\QueryServices\Lender\Deals;

use App\Lender;
use App\Services\QueryServices\AbstractQueryService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class CheckColleaguesPreferences
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class CheckColleaguesPreferences extends AbstractQueryService
{
    /**
     * @var PerfectCloseFit
     */
    private $perfectFitService;

    /**
     * CheckColleaguesPreferences constructor.
     *
     * @param  PerfectCloseFit  $perfectFitService
     */
    public function __construct(PerfectCloseFit $perfectFitService)
    {
        $this->perfectFitService = $perfectFitService;
    }

    /**
     * Returns the Deal IDs that are ignored or archived by the Lender
     *
     * @param  array  $args
     * @return Collection
     */
    public function run(array $args): Collection
    {
        return $this->query($args['colleagues'], $args['connectedBrokers']);
    }

    /**
     * Returns raw query for the Deal IDs that are ignored or archived by the Lender
     *
     * @param  array  $colleagues
     * @param  array  $connectedBrokers
     * @return Collection
     */
    public function query(array $colleagues, array $connectedBrokers, $flag = false): Collection
    {
        $query = DB::table('deals')->select('deals.*')->join('deal_asset_type', 'deals.id', '=', 'deal_asset_type.deal_id');

        $dealsIds = collect();

        $lenders = Lender::with('brokers')->whereIn('id', $colleagues)->get();

        if ($flag) {
            foreach ($colleagues as $colleague) {
                $lender = $lenders->firstWhere('id', $colleague);

                if (! $lender || ! $lender->getPerfectFit()) {
                    continue;
                }
                //Check if this colleague doesn't have connection
                $colleagueBrokers = $lender->brokers->whereIn('id', $connectedBrokers)->pluck('id')->toArray();

                if (empty($colleagueBrokers)) {
                    continue;
                }
                //add only Brokers that are connect to this lender
                $matchedBrokers = array_intersect($connectedBrokers, $colleagueBrokers);
                if (empty($matchedBrokers)) {
                    continue;
                }
                $perfectDeals = $this->perfectFitService->run([
                    'lenderId' => $colleague,
                    'type' => '',
                    'query' => clone $query,
                    'matchedBrokers' => $matchedBrokers,
                ]);

                if ($perfectDeals->isEmpty()) {
                    continue;
                }
                $dealsIds = $dealsIds->merge($perfectDeals);
            }
        } else {
            $brokerColleague = DB::table('broker_lender')->whereIn('broker_lender.broker_id', $connectedBrokers)->pluck('lender_id')->toArray();

            $lenders = Lender::whereIn('id', $colleagues)
                ->whereNotIn('id', $brokerColleague)
                ->get();

            foreach ($lenders as $lender) {
                if (! $lender || ! $lender->getPerfectFit()) {
                    continue;
                }
                // queries the lender, gets the perfectPerfectFit
                $perfectDeals = $this->perfectFitService->run([
                    'lenderId' => $lender->id,
                    'type' => '',
                    'query' => clone $query,
                    'matchedBrokers' => $connectedBrokers,
                ]);

                if ($perfectDeals->isEmpty()) {
                    continue;
                }
                $dealsIds = $dealsIds->merge($perfectDeals);
            }
        }

        return $dealsIds->unique();
    }
}
