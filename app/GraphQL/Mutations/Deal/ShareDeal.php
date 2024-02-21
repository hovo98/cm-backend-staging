<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Deal;

use App\Services\MapperServices\Broker\Deals\ShareDeal as MapperService;
use App\Services\QueryServices\Broker\Deals\ShareDeal as QueryService;
use App\Services\TypeServices\Broker\Deals\ShareDeal as TypeService;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class FilterDeal
 */
class ShareDeal
{
    /**
     * Return a value for the field.
     *
     * @param  null  $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param  mixed[]  $args The arguments that were passed into the field.
     * @param  \Nuwave\Lighthouse\Support\Contracts\GraphQLContext  $context Arbitrary data that is shared between all fields of a single query.
     * @param  \GraphQL\Type\Definition\ResolveInfo  $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     *
     * @throws \Exception
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $broker = $context->user();

        $typeService = new TypeService($args['id'], $args['email'], $broker->id);

        return $typeService->fmap(new QueryService(), new MapperService());
    }
}
