<?php

namespace App\Events;

use App\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $message;

    public $user;

    public function __construct($message, User $user)
    {
        $this->message = $message;
        $this->user = $user;
    }

    public function broadcastOn()
    {
        return ['chat'];
    }

    public function broadcastAs()
    {
        return 'MessageSent';
    }
}
