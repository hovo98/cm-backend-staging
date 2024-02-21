<?php

namespace App\Services\RealTime;

interface RealTimeServiceInterface
{
    public function makeOne();

    public function trigger(string $channel, string $event, array $payload);
}
