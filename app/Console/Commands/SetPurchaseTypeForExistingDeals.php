<?php

namespace App\Console\Commands;

use App\Deal;
use App\DataTransferObjects\Plan;
use App\Enums\DealPurchaseType;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SetPurchaseTypeForExistingDeals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deals:set-purchase-type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $deals = Deal::with('broker')->where('purchase_type', DealPurchaseType::NOT_PURCHASED)->whereNotNull('finished_at')->chunkById(100, function (Collection $deals) {
            foreach ($deals as $deal) {
                if ($deal->broker->subscribed()) {
                    $plan = Plan::fromSubscription($deal->broker->activeSubscription());

                    if ($plan->isValidAmount($deal->getDollarAmount())) {
                        $deal->setPremium(DealPurchaseType::PURCHASED_VIA_SUBSCRIPTION);
                    }
                } elseif ($deal->hasBeenPurchasedByUser($deal->broker)) {
                    $deal->setPremium(DealPurchaseType::PURCHASED_AS_PAY_PER_DEAL);
                }
            }
        });

        return Command::SUCCESS;
    }
}
