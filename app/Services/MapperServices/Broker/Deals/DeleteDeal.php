<?php

declare(strict_types=1);

namespace App\Services\MapperServices\Broker\Deals;

use App\Interfaces\IndividualMapperService;

/**
 * Class DeleteDeal
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class DeleteDeal implements IndividualMapperService
{
    public function map($obj)
    {
        return [
            'status' => true,
        ];
    }
}
