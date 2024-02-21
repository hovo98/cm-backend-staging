<?php

declare(strict_types=1);

namespace App\Services\QueryServices;

/**
 * Class GetManageTable
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class GetManageTable extends AbstractQueryService
{
    public function run($args)
    {
        $user = $args['user'];

        $manageTable = $user->getManageTable($args['table']);

        return [
            'manageTable' => $manageTable,
            'table' => $args['table'],
        ];
    }
}
