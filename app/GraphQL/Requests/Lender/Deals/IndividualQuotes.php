<?php

namespace App\GraphQL\Requests\Lender\Deals;

use App\Services\MapperServices\Lender\Deals\IndividualQuotes as MapperService;
use App\Services\QueryServices\Lender\Deals\IndividualQuotes as QueryService;
use App\Services\TypeServices\Lender\Deals\IndividualQuotes as TypeService;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class IndividualQuotes
{
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array
    {
        $user = $context->user();
        $serviceType = new TypeService($args['deal'], $user);

        return $serviceType->fmap(new QueryService(), new MapperService());
    }
}
