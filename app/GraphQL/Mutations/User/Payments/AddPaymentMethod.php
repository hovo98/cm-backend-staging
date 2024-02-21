<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\User\Payments;

use App\DataTransferObjects\PaymentMethod;
use App\Exceptions\PaymentException;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class AddPaymentMethod
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

        throw_if((!$user || !$user->isBroker()), new PaymentException('user must be a broker'));


        if (!$user->hasStripeId()) {
            $user->createAsStripeCustomer();
        }

        $paymentMethod = $user->addPaymentMethod($args['stripe_id']);

        if ($user->paymentMethods()->count() === 1 && !isset($args['default'])) {
            $user->updateDefaultPaymentMethod($paymentMethod->asStripePaymentMethod());
        }

        if (isset($args['default'])) {
            $user->updateDefaultPaymentMethod($paymentMethod->asStripePaymentMethod());
        }

        return (new PaymentMethod())->mapForBroker($paymentMethod, $user);
    }
}
