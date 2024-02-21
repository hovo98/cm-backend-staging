<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Deal;

use App\Services\MapperServices\Lender\Deals\SaveArchiveDeal as MapperService;
use App\Services\QueryServices\Lender\Deals\SaveArchiveDeal as QueryService;
use App\Services\TypeServices\Lender\Deals\SaveArchiveDeal as TypeService;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class ArchiveDeal
 *
 *
 * @author  Hajdi Djukic Grba <hajdi@forwardslashny.com>
 */
class ArchiveDeal
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
        $checkType = User::LENDER_SAVE_DEAL;
        $msg = 'archived';

        $typeService = new TypeService($args['input'], $user, $type, $checkType, $msg);

        return $typeService->fmap(new QueryService(), new MapperService());
    }
}
