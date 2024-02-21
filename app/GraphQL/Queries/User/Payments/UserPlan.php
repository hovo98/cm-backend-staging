<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\User\Payments;

use App\DataTransferObjects\SubscriptionMapper;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class UserPlan
{
    /**
     * @param $rootValue
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

        $subscription = SubscriptionMapper::map($user->activePlan());

        if (!$subscription) {
            return [];
        }

        return $subscription;
    }
}
