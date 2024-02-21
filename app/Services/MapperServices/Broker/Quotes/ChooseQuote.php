<?php

declare(strict_types=1);

namespace App\Services\MapperServices\Broker\Quotes;

use App\Interfaces\IndividualMapperService;

/**
 * Class ChooseQuote
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class ChooseQuote implements IndividualMapperService
{
    public function map($obj)
    {
        return [
            'status' => $obj['status'],
            'message' => $obj['message'],
        ];
    }
}
