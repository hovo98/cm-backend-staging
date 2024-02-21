<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\User\Payments;

use App\Events\DealPurchased;
use App\DataTransferObjects\Plan;
use App\Payment;
use App\Deal;
use App\Services\Payment\PaymentInterface;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Stripe\Stripe;

class BuyDeal
{
    public function __construct(public PaymentInterface $paymentInterface)
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
        $deal = Deal::find($args['deal_id']);

        Stripe::setApiKey(config('stripe.secret'));

        $plan = new Plan();

        $priceId = $plan->determinePriceId($deal);

        $payload = [
            'line_items' => [
                [
                    'price' => $priceId,
                    'quantity' => 1
                ]
            ],
            'mode' => 'payment',
            'success_url' => $args['success_path']. "/?checkout_id={CHECKOUT_SESSION_ID}&deal_id=$deal->id",
            'cancel_url' =>  $args['cancel_path']. "/?deal_id=$deal->id&checkout_id={CHECKOUT_SESSION_ID}",
        ];

        if (!is_null($user->stripe_id)) {
            $payload = array_merge($payload, ['customer' => $user->stripe_id]);
        } else {
            $payload = array_merge($payload, [
                'customer_email' => $user->email,
                'customer_creation' => 'always',
            ]);
        }

        $response = $this->paymentInterface->createCheckout(array_merge($payload, [
            'metadata' => [
                'type' => 'one-time'
            ]
        ]));

        if ($response) {
            $payment = Payment::create([
                'user_id' => $user->id,
                'stripe_checkout_id' => $response->id,
                'stripe_price_id' => $priceId,
                'deal_id' => $deal->id,
                'expires_at' => $response->expires_at,
                'payment_status' => $response->payment_status
            ]);

            if ($payment->isComplete()) {
                DealPurchased::dispatch($payment);
            }
        }

        return [
            'stripe_id' => $response->id,
            'checkout_url' => $response->url,
            'deal_price' => $plan->getPrice($deal)
        ];
    }
}
