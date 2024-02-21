<?php

namespace App\Jobs;

use App\Deal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BatchLenderNotifyOfDeal implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public Deal $deal;

    public Collection $lenders;

    public array $mappedDeal;

    public string $label;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Deal $deal, Collection $lenders, array $mappedDeal, string $label)
    {
        $this->onQueue('processing');

        $this->deal = $deal;
        $this->lenders = $lenders;
        $this->mappedDeal = $mappedDeal;
        $this->label = $label;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->lenders as $lender) {
            LenderNotifyOfDealIfInterested::dispatchSync($lender, $this->deal, $this->mappedDeal, $this->label);
        }
    }
}
