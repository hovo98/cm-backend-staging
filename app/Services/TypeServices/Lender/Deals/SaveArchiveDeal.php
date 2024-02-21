<?php

declare(strict_types=1);

namespace App\Services\TypeServices\Lender\Deals;

use App\Interfaces\QueryService;
use App\Interfaces\TypeService;
use Illuminate\Foundation\Auth\User;

/**
 * Class SaveArchiveDeal
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class SaveArchiveDeal implements TypeService
{
    public $deals;

    public $lender;

    public $type;

    public $checkType;

    public $msg;

    public function __construct(array $deals, User $lender, int $type, int $checkType, string $msg)
    {
        $this->deals = $deals;
        $this->lender = $lender;
        $this->type = $type;
        $this->checkType = $checkType;
        $this->msg = $msg;
    }

    public function fmap(QueryService $queryService, $mapperService, array $options = [])
    {
        $data = $queryService->run(
            [
                'deals' => $this->deals,
                'lender' => $this->lender,
                'type' => $this->type,
                'checkType' => $this->checkType,
                'msg' => $this->msg,
            ]
        );

        return $mapperService->map($data);
    }
}
