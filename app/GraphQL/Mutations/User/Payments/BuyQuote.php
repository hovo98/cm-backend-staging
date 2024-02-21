<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\User\Payments;

use App\DataTransferObjects\Plan;
use App\Events\DealPurchased;
use App\Payment;
use App\Quote;
use App\Services\Payment\PaymentInterface;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Stripe\Stripe;

class BuyQuote
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
        $quote = Quote::find($args['quote_id']);

        Stripe::setApiKey(config('stripe.secret'));

        $plan = new Plan();

        $priceId = $plan->determinePriceId($quote->deal);

        $payload = [
            'line_items' => [
                [
                    'price' => $priceId,
                    'quantity' => 1
                ]
            ],
            'mode' => 'payment',
            'success_url' => $args['success_path']. "/?checkout_id={CHECKOUT_SESSION_ID}&quote_id=$quote->id",
            'cancel_url' =>  $args['cancel_path']. "/?quote_id=$quote->id&checkout_id={CHECKOUT_SESSION_ID}",
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
                'deal_id' => $quote->deal->id,
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
            'quote_price' => $plan->getPrice($quote->deal)
        ];
    }
}
