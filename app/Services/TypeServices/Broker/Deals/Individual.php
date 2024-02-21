<?php

namespace App\Services\TypeServices\Broker\Deals;

use App\Interfaces\QueryService;
use App\Interfaces\TypeService;

class Individual implements TypeService
{
    public $deal;

    public function __construct(int $deal)
    {
        $this->deal = $deal;
    }

    public function fmap(QueryService $queryService, $mapperService, array $options = [])
    {
        $data = $queryService->run(
            [
                'deal' => $this->deal,
            ]
        );

        return $mapperService->map($data, $this->deal);
    }
}
