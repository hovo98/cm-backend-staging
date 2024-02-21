<?php

namespace App\Console\Commands;

use App\Deal;
use App\Enums\DealPurchaseType;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class DealsMarkExitingFinishedAsPremium extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deals:mark-existing-as-premium';

    /**
     * This command is a one time run when we go live with payments. Existing Deals will be marked as premium and the purchase type will be labeled as not_purchased_free.
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
        $deals = Deal::with('broker')->whereNull('premiumed_at')->whereNotNull('finished_at')->whereDate('finished_at', '<=', '2023-05-15')->chunkById(100, function (Collection $deals) {
            foreach ($deals as $deal) {
                $deal->setPremium(DealPurchaseType::NOT_PURCHASED_FREE);
            }
        });

        return Command::SUCCESS;
    }
}
