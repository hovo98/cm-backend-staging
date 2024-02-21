<?php

namespace Tests\Unit\GraphQL\Mutations\Payments;

use App\Broker;
use App\User;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    /**
     * @return void
     * @group payment
     * @test
     */
    public function user_can_store_payment_methods(): void
    {
        $broker = Broker::factory()->create();

        $response  = $this->actingAs($broker, 'api')
            ->graphQL('
                mutation {
                    paymentMethod (
                        input: {
                            stripe_id: "pm_card_visa",
                        }
                    ) {
                        last_4
                        stripe_payment_id
                        card_type
                        exp_year
                        exp_month
                        default
                    }
                }
            ')
            ->assertOk()
            ->json('data.paymentMethod.default');

        $this->assertTrue($response);
    }

    /**
     * @return void
     * @group payment
     * @test
     */
    public function it_can_list_user_payment_methods(): void
    {
        $broker = Broker::factory()->create();
        $broker->createAsStripeCustomer();

        $broker->addPaymentMethod('pm_card_visa_debit');

        $secondMethod = $broker->addPaymentMethod('pm_card_visa');

        $broker->updateDefaultPaymentMethod($secondMethod->asStripePaymentMethod());

        $this->actingAs($broker, 'api')
            ->graphQL('
               query {
                    paymentMethods {
                        last_4
                        default
                        card_type
                        stripe_payment_id
                        exp_year
                        exp_month
                    }
               }
            ')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'paymentMethods' => [
                        '*' => [
                            'last_4',
                            'default',
                            'card_type',
                            'stripe_payment_id',
                            'exp_year',
                            'exp_month'
                        ]
                    ]
                ]
            ]);
    }

    /**
     * @return void
     * @group payment
     * @test
     */
    public function it_cannot_remove_a_default_payment_method(): void
    {
        $broker = Broker::factory()->create();
        $broker->createAsStripeCustomer();
        $paymentMethod = $broker->addPaymentMethod('pm_card_visa');
        $broker->updateDefaultPaymentMethod($paymentMethod->asStripePaymentMethod());

        $id = $paymentMethod->id;

        $this->actingAs($broker, 'api')
            ->graphQL('
                mutation {
                    deletePaymentMethod (
                        input: {
                            id: "'. $id . '"
                        }
                    ) {
                        last_4
                    }
                }
            ')
            ->assertOk()
            ->assertJsonStructure([
                'errors'
            ]);
    }

    /**
     * @return void
     * @group payment
     * @test
     */
    public function it_cannot_delete_a_primary_payment_method(): void
    {
        $broker = Broker::factory()->create();
        $broker->createAsStripeCustomer();

        $broker->addPaymentMethod('pm_card_visa_debit');

        $secondMethod = $broker->addPaymentMethod('pm_card_visa');

        $broker->updateDefaultPaymentMethod($secondMethod->asStripePaymentMethod());
        $id = $secondMethod->id;

        $this->actingAs($broker, 'api')
            ->graphQL('
                mutation {
                    deletePaymentMethod (
                        input: {
                            id: "'. $id . '"
                        }
                    ) {
                        last_4
                    }
                }
            ')
            ->assertOk()
            ->assertJsonStructure([
                'errors'
            ]);
    }

    /**
     * @return void
     * @group payment
     * @test
     */
    public function can_delete_payment_method(): void
    {
        $broker = Broker::factory()->create();
        $broker->createAsStripeCustomer();

        $firstMethod = $broker->addPaymentMethod('pm_card_visa_debit');
        $secondMethod = $broker->addPaymentMethod('pm_card_visa');

        $broker->updateDefaultPaymentMethod($secondMethod->asStripePaymentMethod());
        $id = $firstMethod->id;

        $this->assertEquals(2, $broker->paymentMethods()->count());

        $this->actingAs($broker, 'api')
            ->graphQL('
                mutation {
                    deletePaymentMethod (
                        input: {
                            id: "'. $id . '"
                        }
                    ) {
                        last_4
                    }
                }
            ')
            ->assertOk();

        $this->assertEquals(1, $broker->paymentMethods()->count());
    }

    /**
     * @return void
     * @test
     */
    public function can_get_payment_plans()
    {
        $broker = User::factory()->create(['role' => 'broker']);

        $this->actingAs($broker, 'api')
            ->graphQL('
                query {
                    plans {
                        name
                        price
                        features
                        description
                        price_id
                        type
                    }
                }
            ')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'plans' => [
                        '*' => [
                            'name',
                            'type',
                            'description',
                            'price',
                            'price_id',
                            'features'
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(8, 'data.plans');
    }
}
