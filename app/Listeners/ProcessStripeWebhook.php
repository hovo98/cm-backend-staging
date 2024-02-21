<?php

namespace App\Listeners;

use App\Jobs\CancelUserSubscription;
use App\Jobs\CompleteOneTimePayment;
use App\Jobs\HandleUserPlanChange;
use App\Jobs\UpdateLimitedDeals;
use App\Jobs\UserChargedSuccessfully;
use App\Jobs\UserSubscribedToPaymentPlan;
use App\StripeWebhook;
use App\User;
use Illuminate\Support\Facades\Bus;

class ProcessStripeWebhook
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $webhook = StripeWebhook::log($event);
        $customerId = data_get($webhook->payload, 'data.object.customer');
        $event = data_get($webhook->payload, 'type');

        if ($event == 'customer.subscription.updated') {
            $user = User::findByStripeId($customerId);
            if ($customerId) {
                if ($user) {
                    $canceled = data_get($webhook->payload, 'data.object.cancel_at_period_end');
                    if ($canceled && $canceled === true) {
                        CancelUserSubscription::dispatch($user);
                    } else {
                        Bus::chain([
                            new HandleUserPlanChange($user, $webhook),
                            new UpdateLimitedDeals($user)
                        ])->dispatch();
                    }
                }
            }
        }

        if ($customerId) {
            $user = User::findByStripeId($customerId);
            if ($user) {
                if ($event == 'customer.subscription.created') {
                    Bus::chain([
                        new UserSubscribedToPaymentPlan($user),
                        new UpdateLimitedDeals($user)
                    ])->dispatch();
                }
            }
        }

        if ($event == 'invoice.payment_succeeded') {
            $price = data_get($webhook->payload, 'data.object.amount_paid');
            $user = User::findByStripeId($customerId);
            UserChargedSuccessfully::dispatch($user, $price);
        }

        if ($event === 'checkout.session.completed') {
            CompleteOneTimePayment::dispatch($webhook);
        }

        if ($event === 'customer.created') {
            $email = data_get($webhook->payload, 'data.object.email');
            $stripeId = data_get($webhook->payload, 'data.object.id');
            $user = User::where('email', $email)->first();
            $user->fill(['stripe_id', $stripeId])->save();
        }

        $webhook->update(['processed_at' => now()]);
    }
}
