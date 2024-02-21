<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Deal;

use App\Services\MapperServices\SetManageTable as MapperService;
use App\Services\QueryServices\SetManageTable as QueryService;
use App\Services\TypeServices\SetManageTable as TypeService;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class SetManageTable
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class SetManageTable
{
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = $context->user();
        $manageTable = $args['input']['manageTable'];
        $table = $args['input']['table'];

        $typeService = new TypeService($manageTable, $table, $user);

        return $typeService->fmap(new QueryService(), new MapperService());
    }
}
