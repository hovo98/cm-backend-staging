<?php

namespace App\GraphQL\Mutations\User\Payments;

use App\Exceptions\PaymentException;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class CreateSubscription
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
        $planId = $args['plan_id'];

        $paymentMethodId = $args['payment_method_id'] ?? null;
        $paymentMethod = $paymentMethodId ? $user->findPaymentMethod($paymentMethodId) : $user->defaultPaymentMethod();

        throw_if(!$user, new PaymentException('user must be a broker'));

        if (!$paymentMethod) {
            throw new PaymentException("User has no default payment method. payment_method_id is required");
        }

        if (!$user->hasStripeId()) {
            $user->createAsStripeCustomer();
        }

        $plans = config('plans');

        $planName = '';

        foreach ($plans as $key => $plan) {
            if (data_get($plan, 'price_id') == $planId) {
                $planName = data_get($plan, 'name');
            }
        }

        throw_if(!$planName, new PaymentException('Payment plan does not exist'));

        $currentPlan = $user->activePlan();

        if ($currentPlan && $currentPlan->name !== $planName) {
            $subscription = $user->subscription($currentPlan->name)->swap($planId);
            if ($subscription) {
                $subscription->update(['name' => $planName]);
            }
        } else {
            $subscription = $user->newSubscription($planName, $planId)->create($paymentMethod->asStripePaymentMethod());
        }

        return [
            'success' => true,
            'name' => $subscription->name,
            'status' => $subscription->stripe_status,
            'stripe_id' => $subscription->stripe_id
        ];
    }
}
