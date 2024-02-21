<?php

namespace App\Listeners;

use App\DataTransferObjects\Plan;
use App\Events\DealPublished;
use App\Enums\DealPurchaseType;

class DealMarkPremiumIfSubscribed
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(DealPublished $event)
    {
        // Skip if not subscribed
        if (! $event->deal->broker->subscribed()) {
            return;
        }

        // Get the Plan
        $plan = Plan::fromSubscription($event->deal->broker->activeSubscription());

        // Confirm deal meets plan limits and set premium
        if ($plan->isValidAmount($event->deal->getDollarAmount())) {
            $event->deal->setPremium(DealPurchaseType::PURCHASED_VIA_SUBSCRIPTION);
        }
    }
}
