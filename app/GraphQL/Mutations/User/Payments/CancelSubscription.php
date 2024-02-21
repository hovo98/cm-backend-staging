<?php

namespace App\GraphQL\Mutations\User\Payments;

use App\Exceptions\PaymentException;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class CancelSubscription
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
        $user = User::find($args['user_id']);
        $planName = $args['plan_name'];

        throw_if(!$user, new PaymentException('user must be a broker'));

        throw_if(!$user->subscribed($planName), new PaymentException('User not subscribed to plan'));

        $subscription = $user->subscription($planName)->cancel();

        return [
            'success' => true,
            'name' => $subscription->name,
            'status' => $subscription->stripe_status,
            'stripe_id' => $subscription->stripe_id
        ];
    }
}
