<?php

declare(strict_types=1);

namespace App\Services\MapperServices;

use App\Interfaces\IndividualMapperService;

/**
 * Class SetManageTable
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class SetManageTable implements IndividualMapperService
{
    public function map($obj)
    {
        return [
            'manageTable' => $obj['manageTable'],
            'table' => $obj['table'],
        ];
    }
}
