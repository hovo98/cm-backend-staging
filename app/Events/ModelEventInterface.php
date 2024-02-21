<?php

namespace App\Events;

use Illuminate\Database\Eloquent\Model;

/**
 * Interface EntityEvent
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
interface ModelEventInterface
{
    /**
     * @return string
     */
    public function name(): string;

    /**
     * @return string
     */
    public function event(): string;

    /**
     * @return Model
     */
    public function model(): Model;
}
