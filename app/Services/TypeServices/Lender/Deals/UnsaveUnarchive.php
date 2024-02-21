<?php

declare(strict_types=1);

namespace App\Services\TypeServices\Lender\Deals;

use App\Interfaces\QueryService;
use App\Interfaces\TypeService;
use Illuminate\Foundation\Auth\User;

/**
 * Class UnsaveUnarchive
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class UnsaveUnarchive implements TypeService
{
    public $deals;

    public $lender;

    public $type;

    public function __construct(array $deals, User $lender, int $type)
    {
        $this->deals = $deals;
        $this->lender = $lender;
        $this->type = $type;
    }

    public function fmap(QueryService $queryService, $mapperService, array $options = [])
    {
        $data = $queryService->run(
            [
                'deals' => $this->deals,
                'lender' => $this->lender,
                'type' => $this->type,
            ]
        );

        return $mapperService->map($data);
    }
}
