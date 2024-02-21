<?php

declare(strict_types=1);

namespace App\GraphQL\Requests\Broker\Quotes;

use App\Services\MapperServices\Broker\Quotes\Individual as MapperService;
use App\Services\QueryServices\Broker\Quotes\Individual as QueryService;
use App\Services\TypeServices\Broker\Quotes\Individual as TypeService;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class Individual
 */
class Individual
{
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array
    {
        $typeService = new TypeService($args['deal'], $args['lender']);

        return $typeService->fmap(new QueryService(), new MapperService());
    }
}
