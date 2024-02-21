<?php

declare(strict_types=1);

namespace App\Services\MapperServices\Lender\Deals;

use App\Interfaces\IndividualMapperService;

/**
 * Class IgnoreDeal
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class IgnoreDeal implements IndividualMapperService
{
    public function map($obj)
    {
        return [
            'status' => $obj['status'],
            'message' => $obj['message'],
        ];
    }
}
