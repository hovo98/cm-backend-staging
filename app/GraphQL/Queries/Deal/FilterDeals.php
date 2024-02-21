<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Deal;

use App\Services\MapperServices\Broker\Deals\FilterDealsBroker as MapperServiceBroker;
use App\Services\MapperServices\Lender\Deals\FilterDealsLender as MapperServiceLender;
use App\Services\QueryServices\Broker\Deals\FilterDealsBroker as QueryServiceBroker;
use App\Services\QueryServices\Lender\Deals\FilterDealsLender as QueryServiceLender;
use App\Services\TypeServices\Broker\Deals\FilterDealsBroker as TypeServiceBroker;
use App\Services\TypeServices\Lender\Deals\FilterDealsLender as TypeServiceLender;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class FilterDeal
 */
class FilterDeals
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
        $perPage = isset($args['pagination']['perPage']) ? $args['pagination']['perPage'] : 1;
        $currentPage = isset($args['pagination']['page']) ? $args['pagination']['page'] : 1;
        $user = $context->user();
        $tags = [];

        $allowedUsers = User::allowedUsersBeta($user->email);
        if (! $allowedUsers) {
            return [];
        }

        foreach ($args as $key => $arg) {
            if ($key === 'context' && $arg === 'general') {
                continue;
            }
            if ($key === 'filterName' && $arg === 'general') {
                continue;
            }
            if ($key === 'sortBy' && $arg['sort'] === 'general') {
                continue;
            }

            if ($key === 'assetTypes' && in_array(0, $arg)) {
                continue;
            }

            if ($key !== 'user_id' && $key !== 'directive' && $key !== 'pagination' && $arg) {
                $tags[$key] = $arg;
            }
        }

        if ($user->role === 'lender') {
            return $this->getDealsForLender($user, $perPage, $currentPage, $args, $tags);
        }

        $queryServiceBroker = resolve(QueryServiceBroker::class);
        $typeServiceBroker = new TypeServiceBroker(
            $user,
            $args['context'],
            $args['searchTerms'],
            $args['loanSize'],
            $args['assetTypes'],
            $args['sortBy'],
            $tags,
            $perPage,
            $currentPage
        );

        $deals = $typeServiceBroker->fmap($queryServiceBroker, new MapperServiceBroker());
        //Return always initial filters
        $typeServiceBrokerInitial = new TypeServiceBroker(
            $user,
            'general',
            '',
            ['min' => 0, 'max' => 200000000],
            [0],
            ['sort' => 'general', 'by' => 'desc'],
            $tags,
            $perPage,
            $currentPage
        );

        $initialAvailableFilters = $typeServiceBrokerInitial->fmap($queryServiceBroker, new MapperServiceBroker());
        $deals['assetTypes'] = $initialAvailableFilters['assetTypes'];
        $deals['dollarAmount'] = $initialAvailableFilters['dollarAmount'];

        // Get Rooms with count of unseen messages
        $rooms = $user->brokerRooms()
            ->withCount(['messages as unseen_messages' => function ($query) {
                $query->whereColumn('messages.user_id', 'rooms.lender_id')->where('seen', false);
            }])
            ->whereIn('rooms.deal_id', collect($deals['data'])->pluck('id')->toArray())
            ->get();

        // Add unseen messages to array
        foreach ($deals['data'] as $key => $deal) {
            $deals['data'][$key]['messages'] = $rooms->where('deal_id', $deal['id'])->sum('unseen_messages');
        }

        return $deals;
    }

    public function getDealsForLender(User $user, int $perPage, int $currentPage, array $args, array $tags): array
    {
        $queryServiceLender = resolve(QueryServiceLender::class);
        $typeServiceLender = new TypeServiceLender(
            $user,
            $args['context'],
            $args['filterName'],
            $args['searchTerms'],
            $args['loanSize'],
            $args['assetTypes'],
            $args['sortBy'],
            $tags,
            $perPage,
            $currentPage
        );

        $deals = $typeServiceLender->fmap($queryServiceLender, new MapperServiceLender());

        //Return always initial filters
        $typeServiceLenderInitial = new TypeServiceLender(
            $user,
            'general',
            '',
            '',
            ['min' => 0, 'max' => 200000000],
            [0],
            ['sort' => 'general', 'by' => 'desc'],
            $tags,
            $perPage,
            $currentPage
        );

        $initialAvailableFilters = $typeServiceLenderInitial->fmap($queryServiceLender, new MapperServiceLender());
        $deals['assetTypes'] = $initialAvailableFilters['assetTypes'];
        $deals['dollarAmount'] = $initialAvailableFilters['dollarAmount'];

        // Get Rooms with count of unseen messages
        $rooms = $user->lenderRooms()
            ->withCount(['messages as unseen_messages' => function ($query) {
                $query->whereColumn('messages.user_id', 'rooms.broker_id')->where('seen', false);
            }])
            ->whereIn('rooms.deal_id', collect($deals['data'])->pluck('id')->toArray())
            ->get();

        // Add unseen messages to array
        foreach ($deals['data'] as $key => $deal) {
            $deals['data'][$key]['messages'] = $rooms->where('deal_id', $deal['id'])->sum('unseen_messages');
        }

        return $deals;
    }
}
