<?php

namespace App\Services\RealTime;

use stdClass;

class DummyRealTimeService implements RealTimeServiceInterface
{
    public function makeOne()
    {
        return new stdClass();
    }

    public function trigger(string $channel, string $event, array $payload)
    {
        return new stdClass();
    }
}
