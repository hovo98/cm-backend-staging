<?php

namespace App\GraphQL\Requests\Lender\Quotes;

use App\Services\MapperServices\Lender\Quote\Individual as MapperService;
use App\Services\QueryServices\Lender\Quotes\Individual as QueryService;
use App\Services\TypeServices\Lender\Quotes\Individual as TypeService;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Individual
{
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array
    {
        $serviceType = new TypeService($args['id']);

        return $serviceType->fmap(new QueryService(), new MapperService());
    }
}
