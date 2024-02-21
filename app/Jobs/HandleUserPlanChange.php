<?php

namespace App\Jobs;

use App\StripeWebhook;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandleUserPlanChange implements ShouldQueue
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
    public function __construct(public User $user, public StripeWebhook $webhook)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $currentPlanValue = (int) data_get($this->webhook->payload, 'data.object.items.data.0.plan.amount') / 100;
        $oldPlanValue = (int) data_get($this->webhook->payload, 'data.previous_attributes.items.data.0.plan.amount') / 100;

        if ($currentPlanValue < $oldPlanValue) {
            $this->user->subscription()->fill([
                'downgraded_at' => now(),
            ])->update();
        }
    }
}
