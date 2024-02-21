<?php

namespace App\Services\TypeServices\Broker\Deals;

use App\Interfaces\QueryService;
use App\Interfaces\TypeService;

class SetTermsheet implements TypeService
{
    protected $deal;

    protected $term;

    public function __construct($deal, $term)
    {
        $this->deal = $deal;
        $this->term = $term;
    }

    public function fmap(QueryService $queryService, $mapperService, array $commands = [])
    {
        $data = $queryService->run([
            'deal' => $this->deal,
            'term' => $this->term,
        ]);

        return $mapperService->map($data);
    }
}
