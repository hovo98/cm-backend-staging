<?php

declare(strict_types=1);

namespace App\Services\MapperServices\Broker\Quotes;

use App\Interfaces\IndividualMapperService;

/**
 * Class SetQuoteStatus
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class SetQuoteStatus implements IndividualMapperService
{
    public function map($obj)
    {
        return [
            'status' => $obj['status'],
            'deal_termsheet_status' => $obj['deal_termsheet_status'],
            'quotes' => $obj['quotes'],
            'message' => $obj['message'],
            'anyQuoteAccepted' => $obj['anyQuoteAccepted'],
        ];
    }
}
