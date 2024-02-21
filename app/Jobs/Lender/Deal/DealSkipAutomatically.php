<?php

declare(strict_types=1);

namespace App\Jobs\Lender\Deal;

use App\Deal;
use App\Jobs\Lender\Deal\DealCreated as JobDealCreated;
use App\Services\QueryServices\Lender\SameDomainLenders;
use App\User;
use App\UserDeals;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class DealSkipAutomatically
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $userDeals;

    public function __construct()
    {
        //Count 3 business days
        $threeBusinessDays = Carbon::now()->subWeekdays(3);
        //Get user deal relations LENDER_DEAL_PUBLISHED that were created 3 business days
        $userDeal = UserDeals::where('relation_type', User::LENDER_DEAL_PUBLISHED)->where('created_at', '<', $threeBusinessDays)->select('user_id', 'deal_id')->get();
        $this->userDeals = $userDeal;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->userDeals->isNotEmpty()) {
            foreach ($this->userDeals as $userDeal) {
                $lender = User::find($userDeal->user_id);
                if ($lender) {
                    $lender->removeRelation($userDeal->deal_id, User::LENDER_DEAL_PUBLISHED);
                    $lender->storeRelationUserDeal($userDeal->deal_id, User::LENDER_IGNORE_DEAL);
                    $this->sendToColleagues($lender, $userDeal->deal_id);
                }
            }
        }
    }

    /**
     * @param $lender
     * @param $dealId
     *
     * Send email to colleagues
     */
    private function sendToColleagues($lender, $dealId)
    {
        $deal = Deal::find($dealId);
        if (!$deal) {
            return;
        }

        $dealMapped = $deal->mappedDeal();
        $sameDomainLenders = resolve(SameDomainLenders::class);
        $lendersWithSameDomain = $sameDomainLenders->run(['id' => $lender->id, 'domain' => preg_replace('/.+@/', '', $lender->email)]);
        // Check if there is no connected lenders and with that deal preferences
        if ($lendersWithSameDomain->isEmpty()) {
            return;
        }
        // Get Lenders with that domain and deal preferences
        $lendersBasedOnPreferences = $deal->getLendersWithDealPreferences($dealMapped, $lendersWithSameDomain, true);

        if ($lendersBasedOnPreferences->isEmpty()) {
            return;
        }

        // For every Lender send them email
        $i = 0;
        $arrOfEmailLender = [];
        foreach ($lendersBasedOnPreferences as $lenderColleague) {
            $i++;
            $arrOfEmailLender[] = $lenderColleague->email;
            JobDealCreated::dispatch($lenderColleague, $dealMapped, false, $i, count($lendersBasedOnPreferences), $arrOfEmailLender);
        }
    }
}
