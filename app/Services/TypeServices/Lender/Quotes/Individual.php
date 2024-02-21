<?php

namespace App\Services\TypeServices\Lender\Quotes;

use App\Interfaces\QueryService;
use App\Interfaces\TypeService;

class Individual implements TypeService
{
    protected $quote;

    public function __construct(int $quote)
    {
        $this->quote = $quote;
    }

    public function fmap(QueryService $queryService, $mapperService, $options = [])
    {
        $data = $queryService->run(
            [
                'quote' => $this->quote,
            ]
        );

        return $mapperService->map($data);
    }
}
