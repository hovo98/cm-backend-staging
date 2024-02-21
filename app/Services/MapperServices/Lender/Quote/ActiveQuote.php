<?php

declare(strict_types=1);

namespace App\Services\MapperServices\Lender\Quote;

use App\Interfaces\IndividualMapperService;

/**
 * Class ActiveQuote
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class ActiveQuote implements IndividualMapperService
{
    public function map($obj)
    {
        return [
            'status' => $obj['status'],
            'message' => $obj['message'],
        ];
    }
}
