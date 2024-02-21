<?php

namespace Tests\Feature\Webhooks;

use App\User;
use Laravel\Cashier\Events\WebhookReceived;
use Laravel\Cashier\Subscription;
use Tests\Mocks\Stripe\SubscriptionDowngradedEvent;
use Tests\Mocks\Stripe\SubscriptionUpdatedEvent;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    /**
     * @return void
     * @test
     */
    public function can_process_subscription_canceled_webhook()
    {
        $payload = SubscriptionUpdatedEvent::get();

        $user = User::factory()->create([
            'role' => 'broker',
            'stripe_id' => 'cus_NtXu5qvDoTiNXp',
        ]);

        $plan = Subscription::factory()->create([
            'user_id' => $user->id,
            'stripe_status' => 'active'
        ]);

        WebhookReceived::dispatch($payload);

        $this->assertEquals('canceled', $plan->refresh()->stripe_status);
    }

    /**
     * @return void
     * @test
     */
    public function can_process_subscription_downgrade_webhook()
    {
        $payload = SubscriptionDowngradedEvent::get();

        $user = User::factory()->create([
            'role' => 'broker',
            'stripe_id' => 'cus_NtXu5qvDoTiNXp',
        ]);

        $plan = Subscription::factory()->create([
            'user_id' => $user->id,
            'stripe_price' => env('STRIPE_EXTRA_LARGE_PRICE_ID'),
            'stripe_status' => 'active'
        ]);

        WebhookReceived::dispatch($payload);

        $this->assertNotNull($plan->refresh()->downgraded_at);
    }
}
