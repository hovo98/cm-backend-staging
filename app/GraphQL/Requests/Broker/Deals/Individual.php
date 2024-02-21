<?php

namespace App\GraphQL\Requests\Broker\Deals;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Individual
{
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array
    {
        $typeService = new \App\Services\TypeServices\Broker\Deals\Individual($args['deal']);

        return $typeService->fmap(new \App\Services\QueryServices\Broker\Deals\Individual(), new \App\Services\MapperServices\Broker\Deals\Individual());
    }
}
