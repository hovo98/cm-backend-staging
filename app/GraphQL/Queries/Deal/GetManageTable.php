<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Deal;

use App\Services\MapperServices\GetManageTable as MapperService;
use App\Services\QueryServices\GetManageTable as QueryService;
use App\Services\TypeServices\GetManageTable as TypeService;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class GetManageTable
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class GetManageTable
{
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = $context->user();
        $table = $args['table'];

        $typeService = new TypeService($table, $user);

        return $typeService->fmap(new QueryService(), new MapperService());
    }
}
