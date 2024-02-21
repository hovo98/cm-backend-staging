<?php

namespace App\Services\TypeServices\Broker\Quotes;

use App\Interfaces\QueryService;
use App\Interfaces\TypeService;

class Individual implements TypeService
{
    public $deal;

    public $lender;

    public function __construct(int $deal, int $lender)
    {
        $this->deal = $deal;
        $this->lender = $lender;
    }

    public function fmap(QueryService $queryService, $mapperService, array $options = [])
    {
        $data = $queryService->run(
            // input for Query service
            [
                'lender' => $this->lender,
                'deal' => $this->deal,
            ]
        );

        return $mapperService->map($data);
    }
}
