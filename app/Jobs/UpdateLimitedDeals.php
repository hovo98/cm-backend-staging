<?php

namespace App\Jobs;

use App\DataTransferObjects\Plan;
use App\Deal;
use App\Enums\DealPurchaseType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateLimitedDeals implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Deal::finishedForBroker($this->user->id)
            ->limitedDeals()
            ->each(function (Deal $deal) {
                $plan = Plan::fromSubscription($this->user->activePlan());
                if($plan->isValidAmount($deal->getDollarAmount())) {
                    $deal->fill(['purchase_type' => DealPurchaseType::PURCHASED_VIA_SUBSCRIPTION])->save();
                }
            });
    }
}
