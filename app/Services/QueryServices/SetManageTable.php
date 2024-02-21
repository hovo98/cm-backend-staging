<?php

declare(strict_types=1);

namespace App\Services\QueryServices;

/**
 * Class SetManageTable
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class SetManageTable extends AbstractQueryService
{
    public function run($args)
    {
        $user = $args['user'];
        $manageTable = $user->updateManageTable($args['manageTable'], $args['table']);

        return [
            'manageTable' => $manageTable,
            'table' => $args['table'],
        ];
    }
}
