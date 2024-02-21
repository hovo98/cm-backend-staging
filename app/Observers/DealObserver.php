<?php

declare(strict_types=1);

namespace App\Observers;

use App\Deal;
use App\Events\DealChanged;
use App\Traits\ModelObserver;

/**
 * Class DealObserver
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
class DealObserver
{
    use ModelObserver;

    /**
     * Handle the deal "created" event.
     *
     * @param  Deal  $deal
     * @return void
     */
    public function created(Deal $deal)
    {
        $this->fireModelEvent($deal, DealChanged::class, 'created');
    }

    /**
     * Handle the deal "created" when deal is published
     *
     * @param  Deal  $deal
     * @return void
     */
    public function createdByUser(Deal $deal)
    {
        $this->fireModelEvent($deal, DealChanged::class, 'createdPublished');
    }

    /**
     * Handle the deal "updated" event.
     *
     * @param  Deal  $deal
     * @return void
     */
    public function updated(Deal $deal)
    {
        $this->fireModelEvent($deal, DealChanged::class, 'updated');
    }

    /**
     * Handle the deal "deleted" event.
     *
     * @param  Deal  $deal
     * @return void
     */
    public function deleted(Deal $deal)
    {
        $this->fireModelEvent($deal, DealChanged::class, 'deleted');
    }

    /**
     * Handle the deal "restored" event.
     *
     * @param  Deal  $deal
     * @return void
     */
    public function restored(Deal $deal)
    {
        $this->fireModelEvent($deal, DealChanged::class, 'restored');
    }

    /**
     * Handle the deal "force deleted" event.
     *
     * @param  Deal  $deal
     * @return void
     */
    public function forceDeleted(Deal $deal)
    {
        $this->fireModelEvent($deal, DealChanged::class, 'forceDeleted');
    }
}
