<?php

declare(strict_types=1);

namespace App\Services\TypeServices;

use App\Interfaces\QueryService;
use App\Interfaces\TypeService;
use Illuminate\Foundation\Auth\User;

/**
 * Class SetManageTable
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class SetManageTable implements TypeService
{
    public $manageTable;

    public $table;

    public $user;

    public function __construct(array $manageTable, string $table, User $user)
    {
        $this->manageTable = $manageTable;
        $this->table = $table;
        $this->user = $user;
    }

    public function fmap(QueryService $queryService, $mapperService, array $options = [])
    {
        $data = $queryService->run(
            [
                'manageTable' => $this->manageTable,
                'table' => $this->table,
                'user' => $this->user,
            ]
        );

        return $mapperService->map($data);
    }
}
