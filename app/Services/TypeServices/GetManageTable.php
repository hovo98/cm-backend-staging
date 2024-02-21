<?php

declare(strict_types=1);

namespace App\Services\TypeServices;

use App\Interfaces\QueryService;
use App\Interfaces\TypeService;
use Illuminate\Foundation\Auth\User;

/**
 * Class GetManageTable
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class GetManageTable implements TypeService
{
    public $table;

    public $user;

    public function __construct(string $table, User $user)
    {
        $this->table = $table;
        $this->user = $user;
    }

    public function fmap(QueryService $queryService, $mapperService, array $options = [])
    {
        $data = $queryService->run(
            [
                'table' => $this->table,
                'user' => $this->user,
            ]
        );

        return $mapperService->map($data);
    }
}
