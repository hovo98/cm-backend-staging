<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Deal;

use App\Services\MapperServices\Lender\Deals\UnsaveUnarchive as MapperService;
use App\Services\QueryServices\Lender\Deals\UnsaveUnarchive as QueryService;
use App\Services\TypeServices\Lender\Deals\UnsaveUnarchive as TypeService;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class UnarchivedDeal
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class UnarchivedDeal
{
    /**
     * @param    $rootValue
     * @param  array  $args
     * @param  GraphQLContext|null  $context
     * @param  ResolveInfo  $resolveInfo
     * @return array
     *
     * @throws \Exception
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $user = $context->user();
        $type = User::LENDER_ARCHIVE_DEAL;

        $typeService = new TypeService($args['input'], $user, $type);

        return $typeService->fmap(new QueryService(), new MapperService());
    }
}
