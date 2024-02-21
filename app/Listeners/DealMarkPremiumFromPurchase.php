<?php

namespace App\Listeners;

use App\Events\DealPurchased;
use App\Enums\DealPurchaseType;

class DealMarkPremiumFromPurchase
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(DealPurchased $event)
    {
        $event->payment->deal->setPremium(DealPurchaseType::PURCHASED_AS_PAY_PER_DEAL);
    }
}
