<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Deal;

use App\Services\MapperServices\Lender\Deals\IgnoreDeal as MapperService;
use App\Services\QueryServices\Lender\Deals\IgnoreDeal as QueryService;
use App\Services\TypeServices\Lender\Deals\IgnoreDeal as TypeService;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class IgnoreDeal
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class IgnoreDeal
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
        if (! isset($args['token'])) {
            $deal_id = $args['deal_id'];
        } else {
            $payload = decrypt($args['token']);
            $user = User::whereId($payload['id'])->whereEmail($payload['email'])->first();
            $deal_id = $payload['deal_id'];
        }

        $typeService = new TypeService($user, $deal_id);

        return $typeService->fmap(new QueryService(), new MapperService());
    }
}
