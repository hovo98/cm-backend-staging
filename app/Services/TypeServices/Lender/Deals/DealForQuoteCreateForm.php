<?php

namespace App\Services\TypeServices\Lender\Deals;

use App\Interfaces\QueryService;
use App\Interfaces\TypeService;

class DealForQuoteCreateForm implements TypeService
{
    protected $deal;

    public function __construct($deal)
    {
        $this->deal = $deal;
    }

    public function fmap(QueryService $queryService, $mapperService, array $commands = [])
    {
        // TODO: Implement fmap() method.
        $data = $queryService->run([
            'id' => $this->deal,
        ]);

        return $mapperService->map($data);
    }
}
