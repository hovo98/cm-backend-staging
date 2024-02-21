<?php

namespace Tests\Unit\GraphQL\Mutations\Payments;

use App\User;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    /**
     * @return void
     * @group payment
     * @test
     */
    public function broker_can_subscribe_to_a_plan(): void
    {
        $broker = User::factory()->create(['role' => 'broker']);
        $broker->createAsStripeCustomer();

        $paymentMethod = $broker->addPaymentMethod('pm_card_visa_debit');
        $broker->updateDefaultPaymentMethod($paymentMethod->asStripePaymentMethod()->id);
        $broker->updateDefaultPaymentMethodFromStripe();

        $this->actingAs($broker, 'api')
            ->graphQL('
                mutation {
                    createSubscription (
                        input: {
                            plan_id: "price_1MtXiVAyqeO9IwLwExBohum0"
                        }
                    ) {
                        success,
                        stripe_id,
                    }
                }
            ')
            ->assertOk();

        $this->assertCount(1, $broker->subscriptions()->get());
    }

    /**
     * @return void
     * @test
     * @group payment
     */
    public function cannot_subscribe_to_plan_without_payment_method(): void
    {
        $broker = User::factory()->create(['role' => 'broker']);
        $broker->createAsStripeCustomer();

        $this->actingAs($broker, 'api')
            ->graphQL('
                mutation {
                    createSubscription (
                        input: {
                            plan_id: "price_1MtXiVAyqeO9IwLwExBohum0"
                        }
                    ) {
                        success,
                        stripe_id,
                    }
                }
            ')
            ->assertOk()
            ->assertJson([
                'errors' => [
                    [
                        'message' => 'User has no default payment method. payment_method_id is required'
                    ]
                ]
            ]);
    }

    /**
     * @return void
     * @group payment
     * @test
     */
    public function user_can_swap_subscriptions(): void
    {
        $broker = User::factory()->create(['role' => 'broker']);
        $broker->createAsStripeCustomer();

        $paymentMethod = $broker->addPaymentMethod('pm_card_visa_debit');
        $broker->newSubscription('small', 'price_1MtXiVAyqeO9IwLwExBohum0')->create($paymentMethod->id);

        $largePlanId = "price_1MtXjmAyqeO9IwLwwWKS2B36";

        $this->actingAs($broker, 'api')
            ->graphQL('
                mutation {
                    createSubscription (
                        input: {
                            plan_id: "'. $largePlanId .'"
                        }
                    ) {
                        success,
                        stripe_id,
                    }
                }
            ')
            ->assertOk();

        $this->assertEquals('Large', $broker->activePlan()->name);
    }

    /**
     * @return void
     * @group payment
     * @test
     */
    public function user_should_be_able_to_cancel_active_subscription(): void
    {
        $broker = User::factory()->create(['role' => 'broker']);
        $broker->createAsStripeCustomer();

        $paymentMethod = $broker->addPaymentMethod('pm_card_visa_debit');
        $broker->newSubscription('small', 'price_1MtXiVAyqeO9IwLwExBohum0')->create($paymentMethod->id);

        $this->actingAs($broker, 'api')
            ->graphQL(
                '
                    mutation {
                        cancelSubscription (
                            input: {
                                plan_name: "small"
                            }
                        ) {
                            success
                            stripe_id
                            status
                        }
                    }
                '
            )
            ->assertOk();

        $currentPLan = $broker->activePlan();

        $this->assertNotNull($currentPLan->ends_at);
    }
}
