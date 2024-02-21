<?php

declare(strict_types=1);

namespace App\GraphQL\Builders;

use App\Broker;

/**
 * Class Brokers
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
class Brokers
{
    //Returns only Users where role is broker
    public function __invoke()
    {
        return Broker::query()
            ->where('role', '=', 'broker');
    }
}
