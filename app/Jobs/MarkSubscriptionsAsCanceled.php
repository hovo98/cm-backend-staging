<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Cashier\Subscription;

class MarkSubscriptionsAsCanceled implements ShouldQueue
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
    public function __construct(public Collection $subscriptions)
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
        $this->subscriptions->each(function (Subscription $subscription) {
            if ($subscription->trial_ends_at?->isToday() || $subscription->pastDue()) {

                if ($subscription->owner->hasPaymentMethod()) {
                    $subscription->endTrial();
                }

                $subscription->fill([
                    'stripe_status' =>  \Stripe\Subscription::STATUS_CANCELED
                ])->save();
            }

            if ($subscription->ends_at?->isToday()) {
                $subscription->cancel();
            }
        });
    }
}
