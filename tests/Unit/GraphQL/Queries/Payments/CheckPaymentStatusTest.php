<?php

namespace Tests\Unit\GraphQL\Queries\Payments;

use App\Broker;
use App\Deal;
use App\Payment;
use App\Lender;
use App\Quote;
use App\Termsheet;
use App\User;
use Database\Seeders\Termsheets;
use Tests\Mocks\Queries\CheckPaymentStatusQuery;
use Tests\TestCase;

class CheckPaymentStatusTest extends TestCase
{
    /**
     * @return void
     * @test
     */
    public function can_check_status_of_a_payment_request(): void
    {
        $broker = User::factory()->create(['role' => 'broker']);
        $lender = User::factory()->create(['role' => 'lender']);
        $user = User::factory()->create(['role' => 'lender']);

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();

        $deal = Deal::factory()->create([
            'user_id' => $broker->id,
            'dollar_amount' => 15000000
        ]);
        $user->storeRelationUserDeal($deal->id, User::LENDER_IGNORE_DEAL);

        $quote = Quote::factory()->create([
            'user_id' => $lender->id,
            'finished' => true,
            'finished_at' => now(),
            'deal_id' => $deal->id,
            'dollar_amount' => 1000000
        ]);

        Payment::factory()
            ->for($broker, 'user')
            ->for($deal)
            ->paid()
            ->create([
                'stripe_checkout_id' => "cs_test_a1VauZcr0tnFuHpjuRPvgbLl84LO1VU5hYHWLlro7Q3Z8pTn0bv4PTwqYN"
            ]);

        $this->actingAs($broker, 'api')
            ->graphQL('
                query {
                    checkPaymentStatus (
                        input: {
                            checkout_id: "cs_test_a1VauZcr0tnFuHpjuRPvgbLl84LO1VU5hYHWLlro7Q3Z8pTn0bv4PTwqYN"
                        }
                    ) {
                        status
                    }
                }
            ')
            ->assertOk()
            ->assertJsonStructure([
                "data" => [
                    'checkPaymentStatus' => [
                        'status'
                    ]
                ]
            ]);
    }

    /**
     * @test
     */
    public function we_can_mark_a_deal_premium_when_payment_is_completed()
    {
        $broker = Broker::factory()->withCompany()->create();
        $lender = Lender::factory()->create();

        $this->setupTermSheets();

        $deal = Deal::factory()->for($broker)->pricedAt(2000000)->published()->create();

        $quote = Quote::factory()->for($deal)->for($lender)->finished()->create();

        $payment = Payment::factory()->for($broker, 'user')->for($deal)->unpaid()->create();

        $this->assertNull($deal->premiumed_at);
        $this->assertFalse($deal->isPremium());
        $this->assertFalse($payment->isComplete());
        $this->assertEquals('unpaid', $payment->payment_status);

        $this->actingAs($broker, 'api')
            ->graphQL(
                new CheckPaymentStatusQuery(
                    stripeCheckoutId: $payment->stripe_checkout_id,
                )
            )
            ->assertOk()
            ->assertJson([
                'data' => [
                    'checkPaymentStatus' => [
                        'status' => 'completed',
                    ],
                ],
            ]);

        $this->assertNotNull($deal->fresh()->premiumed_at);
        $this->assertTrue($deal->fresh()->isPremium());
        $this->assertTrue($payment->fresh()->isComplete());
        $this->assertEquals('paid', $payment->fresh()->payment_status);
    }
}
