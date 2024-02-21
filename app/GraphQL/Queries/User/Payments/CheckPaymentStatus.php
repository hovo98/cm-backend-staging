<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\User\Payments;

use App\Events\DealPurchased;
use App\Exceptions\PaymentException;
use App\Payment;
use App\Services\Payment\PaymentInterface;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class CheckPaymentStatus
{
    public function __construct(public PaymentInterface $paymentService)
    {
    }

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

        /** @var Payment $payment */
        $payment = Payment::where('stripe_checkout_id', $args['checkout_id'])->first();

        if (!$payment) {
            throw new PaymentException("payment with checkout_id not found");
        }

        $response = $this->paymentService->retrieveCheckout($payment->stripe_checkout_id);

        throw_if(!$response, new PaymentException('stripe checkout session not found'));

        $payment->update([
            'payment_status' => $response->payment_status,
            'processed' => true
        ]);

        if ($payment->isComplete()) {
            DealPurchased::dispatch($payment);
        }

        return [
            'status' => $payment->refresh()->payment_status === 'paid' ? 'completed' : 'uncompleted',
        ];
    }
}
