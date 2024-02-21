<?php

declare(strict_types=1);

namespace App\Services\TypeServices\Lender\Deals;

use App\Interfaces\QueryService;
use App\Interfaces\TypeService;

/**
 * Class IgnoreDeal
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class IgnoreDeal implements TypeService
{
    public $user;

    public $deal_id;

    public function __construct($user, $deal_id)
    {
        $this->user = $user;
        $this->deal_id = $deal_id;
    }

    public function fmap(QueryService $queryService, $mapperService, array $options = [])
    {
        $data = $queryService->run(
            [
                'user' => $this->user,
                'deal_id' => $this->deal_id,
            ]
        );

        return $mapperService->map($data);
    }
}
