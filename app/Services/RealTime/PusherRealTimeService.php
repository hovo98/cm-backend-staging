<?php

namespace App\Services\RealTime;

use Pusher\Pusher;

class PusherRealTimeService implements RealTimeServiceInterface
{
    public function makeOne()
    {
        return new Pusher(
            config('app.pusher_key'),
            config('app.pusher_secret'),
            config('app.pusher_app_id'),
            ['cluster' => 'mt1']
        );
    }

    public function trigger(string $channel, string $event, array $payload)
    {
        return $this->makeOne()->trigger($channel, $event, $payload);
    }
}
