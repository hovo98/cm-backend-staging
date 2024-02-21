<?php

declare(strict_types=1);

namespace App\Services\MapperServices\Lender\Deals;

use App\Interfaces\IndividualMapperService;

/**
 * Class UnsaveUnarchive
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class UnsaveUnarchive implements IndividualMapperService
{
    public function map($obj)
    {
        return [
            'status' => $obj['status'],
        ];
    }
}
