<?php

namespace App\Services\TypeServices\Lender\Deals;

use App\Interfaces\QueryService;
use App\Interfaces\TypeService;

class IndividualQuotes implements TypeService
{
    protected $deal;

    protected $user;

    public function __construct($deal, $user)
    {
        $this->deal = $deal;
        $this->user = $user;
    }

    public function fmap(QueryService $queryService, $mapperService, array $commands = [])
    {
        $data = $queryService->run([
            'deal' => $this->deal,
            'user' => $this->user->id,
        ]);

        //        dd($data);

        return $mapperService->map($data);
    }
}
