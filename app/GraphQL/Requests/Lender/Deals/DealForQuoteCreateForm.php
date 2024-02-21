<?php

namespace App\GraphQL\Requests\Lender\Deals;

use App\Services\MapperServices\Lender\Deals\DealForQuoteCreateForm as MapperService;
use App\Services\QueryServices\Lender\Deals\DealForQuoteCreateForm as QueryService;
use App\Services\TypeServices\Lender\Deals\DealForQuoteCreateForm as TypeService;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class DealForQuoteCreateForm
{
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array
    {
        $serviceType = new TypeService($args['id']);

        return $serviceType->fmap(new QueryService(), new MapperService());
    }
}
