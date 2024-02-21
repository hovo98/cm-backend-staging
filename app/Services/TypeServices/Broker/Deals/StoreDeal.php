<?php

namespace App\Services\TypeServices\Broker\Deals;

use App\Interfaces\QueryService;
use Illuminate\Support\Facades\Log;

class StoreDeal implements \App\Interfaces\TypeService
{
    // TODO not in use, finish implementation or remove
    public $user;

    public $args;

    public function __construct($user, $args)
    {
        $this->user = $user;
        $this->args = $args;
    }

    public function fmap(QueryService $queryService, $mapperService, array $commands = [])
    {
        try {
            $deal = $queryService->run(
                [
                    'user' => $this->user,
                    'request_args' => $this->args,
                ]
            );
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
            exit;
        }

        return $mapperService->map($deal);
    }
}
