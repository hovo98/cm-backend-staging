<?php

declare(strict_types=1);

namespace App\Events;

use App\Traits\ModelEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class DealMetaChanged
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
class DealMetaChanged implements ModelEventInterface
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;
    use ModelEvent;
}
