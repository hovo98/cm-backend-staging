<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait ModelEvent
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
trait ModelEvent
{
    /** @var Model */
    private $model;

    /** @var string */
    private $modelEvent;

    /**
     * Create a new event instance.
     *
     * @param  Model  $model
     * @param  string  $modelEvent
     */
    public function __construct(Model $model, string $modelEvent)
    {
        $this->model = $model;
        $this->modelEvent = $modelEvent;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }

    /**
     * Event class FQN
     *
     * @return string
     */
    public function name(): string
    {
        return get_class();
    }

    /**
     * Instance of the Model which caused the Event
     *
     * @return Model
     */
    public function model(): Model
    {
        return $this->model;
    }

    /**
     * Model Event which occurred
     *
     * @return string
     */
    public function event(): string
    {
        return $this->modelEvent;
    }
}
