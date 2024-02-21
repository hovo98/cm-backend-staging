<?php

declare(strict_types=1);

namespace App\Services\TypeServices\Broker\Deals;

use App\Interfaces\QueryService;
use App\Interfaces\TypeService;

/**
 * Class DeleteDeal
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class DeleteDeal implements TypeService
{
    public $deals;

    public $broker;

    public function __construct(array $deals, int $broker)
    {
        $this->deals = $deals;
        $this->broker = $broker;
    }

    public function fmap(QueryService $queryService, $mapperService, array $options = [])
    {
        $data = $queryService->run(
            [
                'deals' => $this->deals,
                'broker' => $this->broker,
            ]
        );

        return $mapperService->map($data);
    }
}
