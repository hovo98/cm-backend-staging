<?php

namespace App\GraphQL\Requests\Broker\Deals;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class SetTermsheet
{
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array
    {
        $typeService = new \App\Services\TypeServices\Broker\Deals\SetTermsheet($args['deal'], $args['term']);

        return $typeService->fmap(new \App\Services\QueryServices\Broker\Deals\SetTermsheet(), new \App\Services\MapperServices\Broker\Deals\SetTermsheet());
    }
}
