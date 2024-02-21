<?php

declare(strict_types=1);

namespace App\Services\TypeServices\Broker\Quotes;

use App\Interfaces\QueryService;
use App\Interfaces\TypeService;
use Illuminate\Foundation\Auth\User;

/**
 * Class ChooseQuote
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class ChooseQuote implements TypeService
{
    public $broker;

    public $quoteId;

    public $dealId;

    public $chooseBoth;

    public function __construct(User $broker, int $quoteId, int $dealId, bool $chooseBoth)
    {
        $this->broker = $broker;
        $this->quoteId = $quoteId;
        $this->dealId = $dealId;
        $this->chooseBoth = $chooseBoth;
    }

    public function fmap(QueryService $queryService, $mapperService, array $options = [])
    {
        $data = $queryService->run(
            [
                'broker' => $this->broker,
                'quoteId' => $this->quoteId,
                'dealId' => $this->dealId,
                'chooseBoth' => $this->chooseBoth,
            ]
        );

        return $mapperService->map($data);
    }
}
