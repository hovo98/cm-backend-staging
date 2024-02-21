<?php

namespace App\Listeners;

use App\Events\DealPublished;
use App\Jobs\DealNotifyInterestedLenders;

class NotifyPublishedDeal
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(DealPublished $event)
    {
        DealNotifyInterestedLenders::dispatch($event->deal);
    }
}
