<?php

declare(strict_types=1);

namespace App\Services\MapperServices\Broker\Deals;

use App\Interfaces\IndividualMapperService;

/**
 * Class ShareDeal
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class ShareDeal implements IndividualMapperService
{
    public function map($obj)
    {
        return [
            'errorMessage' => $obj['errorMessage'],
        ];
    }
}
