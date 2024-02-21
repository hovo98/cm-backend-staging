<?php

declare(strict_types=1);

namespace App\Services\TypeServices\Lender\Quotes;

use App\Interfaces\QueryService;
use App\Interfaces\TypeService;
use Illuminate\Foundation\Auth\User;

/**
 * Class ActiveQuote
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class ActiveQuote implements TypeService
{
    public $lender;

    public $quoteId;

    public $dealId;

    public $is_active;

    public function __construct(User $lender, int $quoteId, int $dealId, bool $is_active)
    {
        $this->lender = $lender;
        $this->quoteId = $quoteId;
        $this->dealId = $dealId;
        $this->is_active = $is_active;
    }

    public function fmap(QueryService $queryService, $mapperService, array $options = [])
    {
        $data = $queryService->run(
            [
                'lender' => $this->lender,
                'quoteId' => $this->quoteId,
                'dealId' => $this->dealId,
                'is_active' => $this->is_active,
            ]
        );

        return $mapperService->map($data);
    }
}
