<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\User\Payments;

use App\DataTransferObjects\PaymentMethod;
use App\Exceptions\PaymentException;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class DeletePaymentMethod
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

        $paymentMethod = $user->findPaymentMethod($args['id']);

        throw_if(
            $user->paymentMethods()->count() === 1,
            new PaymentException("cannot delete only payment method")
        );

        throw_if(!$paymentMethod, new PaymentException("payment method not found"));

        throw_if(
            $paymentMethod->card->last4 === $user->pm_last_four,
            new PaymentException("cannot deleted a default primary method")
        );

        $user->deletePaymentMethod($paymentMethod->id);

        return (new PaymentMethod())->mapForBroker($paymentMethod, $user);
    }
}
