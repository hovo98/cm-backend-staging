<?php

declare(strict_types=1);

namespace App\Interfaces;

use Illuminate\Database\Query\Builder;

/**
 * Interface QueryService
 *
 * @author  Vladislav Mosnak <vlada@forwardslashny.com>
 */
interface QueryService
{
    public function run(array $args);

    public function paginate(Builder $query): array;
}
