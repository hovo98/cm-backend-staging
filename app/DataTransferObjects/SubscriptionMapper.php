<?php

namespace App\DataTransferObjects;

use App\Services\Payment\PaymentInterface;
use Carbon\Carbon;
use Stripe\Subscription;

class SubscriptionMapper
{
    public static function map($subscription)
    {
        if (!$subscription) {
            return null;
        }

        $nextBillingDate = static::getNextBillingDate($subscription);

        return [
            'name' => (new \App\DataTransferObjects\Plan())->getPlanName($subscription->stripe_price),
            'status' => $subscription->stripe_status,
            'stripe_id' => $subscription->stripe_id,
            'ends_at' => $subscription->stripe_status === Subscription::STATUS_TRIALING
                ? $subscription->trial_ends_at?->toDateString()
                : $subscription->ends_at?->toDateString(),
            'downgraded_message' => static::getDowngradedMessage($subscription, $nextBillingDate),
            'next_billing_date' => $nextBillingDate,
            'next_billing_amount' => static::getNextBillingAmount($subscription),
        ];
    }

    private static function getNextBillingDate($subscription)
    {
        /** @var PaymentInterface $paymentService */
        $paymentService = app()->make(PaymentInterface::class);

        return $subscription->stripe_status === 'active' ? $paymentService->nextBillingDate($subscription->user->stripe_id) : null;
    }

    private static function getNextBillingAmount($subscription)
    {
        /** @var PaymentInterface $paymentService */
        $paymentService = app()->make(PaymentInterface::class);

        return  $subscription->stripe_status === 'active' ? $paymentService->nextBillingAmount($subscription->user->stripe_id) : null;
    }

    private static function getDowngradedMessage($subscription, $nextBillingDate = null)
    {
        $timestamp = isset($nextBillingDate) ? Carbon::parse($nextBillingDate) : null;

        if($subscription->downgraded_at && $timestamp && !$timestamp->isPast()) {
            return "The downgrade will be applied on the next billing cycle.";
        }

        return null;
    }
}
