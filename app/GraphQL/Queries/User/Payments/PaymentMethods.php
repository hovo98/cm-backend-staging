<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\User\Payments;

use App\DataTransferObjects\PaymentMethod;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class PaymentMethods
{
    public function resolve($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $broker = User::find($args['user_id']);

        if (!$broker->hasStripeId()) {
            // throw error
        }

        $paymentMethods = $broker->paymentMethods();

        return (new PaymentMethod())->mapForBroker($paymentMethods, $broker);
    }
}
