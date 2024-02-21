<?php

declare(strict_types=1);

namespace App\Services\TypeServices\Broker\Deals;

use App\Interfaces\QueryService;
use App\Interfaces\TypeService;

/**
 * Class ShareDeal
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class ShareDeal implements TypeService
{
    public $deals;

    public $emails;

    public $broker;

    public function __construct(array $deals, array $emails, int $broker)
    {
        $this->deals = $deals;
        $this->emails = $emails;
        $this->broker = $broker;
    }

    public function fmap(QueryService $queryService, $mapperService, array $options = [])
    {
        $data = $queryService->run(
            [
                'deals' => $this->deals,
                'emails' => $this->emails,
                'broker' => $this->broker,
            ]
        );

        return $mapperService->map($data);
    }
}
