<?php

namespace App\Jobs;

use App\Deal;
use App\Lender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class DealNotifyInterestedLenders implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public Deal $deal;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Deal $deal)
    {
        $this->deal = $deal;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get Broker's connections
        $connectedLenders = $this->deal->broker->lenders()
            ->with('company')
            ->betaUser()
            ->whereNotNull(DB::raw("metas::jsonb->'perfect_fit'"))
            ->get();

        // Map Deal info
        $mappedDeal = $this->deal->mappedDeal();

        // Dispatch for Connected Lenders
        $connectedLenders->each(function ($lender) use ($mappedDeal) {
            LenderNotifyOfDealIfInterested::dispatch($lender, $this->deal, $mappedDeal, 'connected');
        });

        // Don't email others in the same company
        $excludedCompanyIds = $connectedLenders->map(fn ($lender) => $lender->company->id)->toArray();

        $params = $this->getDealPreferencesParams($mappedDeal, $excludedCompanyIds);
        $allLenders = Lender::getByPreferencesQuery(...$params)->chunkById(200, function ($lenders) use ($mappedDeal) {
            BatchLenderNotifyOfDeal::dispatch($this->deal, $lenders, $mappedDeal, 'publish');
        });
    }

    /**
     * @return array of formatted params to find matching Lenders
     */
    public function getDealPreferencesParams(array $mappedDeal, array $excludedCompanyIds): array
    {
        // Get lenders based on deal preferences
        $dealDollarAmount = (int) $mappedDeal['inducted']['loan_amount'];

        $dealLocations = [
            $mappedDeal['location']['state'],
            $mappedDeal['location']['city'],
            $mappedDeal['location']['sublocality'],
            $mappedDeal['location']['county'],
            $mappedDeal['location']['country'],
        ];

        $dealAssetTypes = $mappedDeal['inducted']['property_type']['asset_types'];

        return [
            $dealDollarAmount,
            $dealLocations,
            $dealAssetTypes,
            ['excluded_company_ids' => $excludedCompanyIds],
        ];
    }
}
