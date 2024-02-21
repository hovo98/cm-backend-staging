<?php

declare(strict_types=1);

namespace App\GraphQL\Requests\Broker\Quotes;

use App\Services\MapperServices\Broker\Quotes\AllQuotes as MapperService;
use App\Services\QueryServices\Broker\Quotes\AllQuotes as QueryService;
use App\Services\TypeServices\Broker\Quotes\AllQuotes as TypeService;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class AllQuotes
 */
class AllQuotes
{
    /**
     * @param $rootValue
     * @param  array  $args
     * @param  GraphQLContext  $context
     * @param  ResolveInfo  $resolveInfo
     * @return array
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): array
    {
        $user = $context->user();
        $tags = [];

        $allowedUsers = User::allowedUsersBeta($user->email);
        if (! $allowedUsers) {
            return [];
        }

        foreach ($args as $key => $arg) {
            if ($key === 'sponsorNames' && in_array('', $arg)) {
                continue;
            }
            if ($key === 'sortBy' && $arg['sort'] === 'general') {
                continue;
            }
            if ($key !== 'user_id' && $key !== 'directive' && $key !== 'pagination' && $arg) {
                $tags[$key] = $arg;
            }
        }

        $queryService = resolve(QueryService::class);

        $currentPage = isset($args['pagination']['perPage']) ? $args['pagination']['perPage'] : 1;
        $page = isset($args['pagination']['page']) ? $args['pagination']['page'] : 1;
        $typeService = new TypeService($user, $args['searchLocation'], $args['sponsorNames'], $args['sponsorName'], $args['sortBy'], $tags, $page, $currentPage);

        $quotes = $typeService->fmap($queryService, new MapperService());
        //Return initial sponsor names
        $typeServiceBrokerInitial = new TypeService($user, '', [''], '', ['sort' => 'general', 'by' => 'desc'], $tags, $page, $currentPage);
        $initialAvailableFilters = $typeServiceBrokerInitial->fmap($queryService, new MapperService());
        $quotes['sponsorNames'] = $initialAvailableFilters['sponsorNames'];

        return $quotes;
    }
}
