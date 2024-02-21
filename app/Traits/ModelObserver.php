<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

/**
 * Trait ModelObserver
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
trait ModelObserver
{
    /**
     * @param  Model  $quoteMeta
     * @param  string  $event  Event classFQN, must implement App\Events\ModelEventInterface
     * @param  string  $modelEvent
     */
    private function fireModelEvent(Model $quoteMeta, string $event, string $modelEvent): void
    {
        event(new $event($quoteMeta, $modelEvent));
    }
}
