<?php

declare(strict_types=1);

namespace App\Services\TypeServices\Broker\Quotes;

use App\Interfaces\QueryService;
use App\Interfaces\TypeService;
use Illuminate\Foundation\Auth\User;

/**
 * Class SetQuoteStatus
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class SetQuoteStatus implements TypeService
{
    public $broker;

    public $quoteId;

    public $statusType;

    public $unacceptMessage;

    public function __construct(User $broker, int $quoteId, int $statusType, string $unacceptMessage = null)
    {
        $this->broker = $broker;
        $this->quoteId = $quoteId;
        $this->statusType = $statusType;
        $this->unacceptMessage = $unacceptMessage;
    }

    public function fmap(QueryService $queryService, $mapperService, array $options = [])
    {
        if ($this->unacceptMessage === null) {
            $data = $queryService->run(
                [
                    'broker' => $this->broker,
                    'quoteId' => $this->quoteId,
                    'statusType' => $this->statusType,
                ]
            );
        } else {
            $data = $queryService->run(
                [
                    'broker' => $this->broker,
                    'quoteId' => $this->quoteId,
                    'statusType' => $this->statusType,
                    'unacceptMessage' => $this->unacceptMessage,
                ]
            );
        }

        return $mapperService->map($data);
    }
}
