<?php

namespace Tests\Feature;

use App\Broker;
use App\Deal;
use App\Lender;
use App\Payment;
use App\Quote;
use App\Termsheet;
use App\User;
use Database\Seeders\Termsheets;
use GraphQL\Error\Error;
use Laravel\Cashier\Subscription;
use Tests\Mocks\DealData;
use Tests\TestCase;

class StoreQuotesTest extends TestCase
{
    /**
     * @test
     * @return void
     */
    public function it_sets_quotes_limit()
    {
        $user = User::factory()->create();
        $broker = Broker::factory()->create();
        $lender = Lender::factory()->create();

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();
        $deal = Deal::factory()->create(['user_id' => $broker->id]);
        $user->storeRelationUserDeal($deal->id, User::LENDER_IGNORE_DEAL);

        Quote::factory()->count(2)->create([
            'user_id' => $lender->id,
            'finished' => true,
            'finished_at' => now(),
            'deal_id' => $deal->id,
        ]);

        $quote = Quote::factory()->create([
            'finished' => false,
            'deal_id' => $deal->id,
            'user_id' => $lender->id,
        ]);

        $this->actingAs($user, 'api')
            ->graphQL(/** @lang GraphQL */ '
           mutation {
              quote (
                    input: {
                        id: '. $quote->id .'
                        deal: {
                            id: '. $deal->id . '
                        }
                        finished: true
                    }
               ) {
                 id
              }
           }
        ')
            ->assertOk();

        $this->assertTrue($deal->refresh()->quote_limit_reached);
    }

    /**
     * @return void
     * @test
     */
    public function user_can_accept_quote(): void
    {
        $user = User::factory()->create();
        $broker = User::factory()->create(['role' => 'broker']);
        $lender = User::factory()->create(['role' => 'lender']);

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();

        $deal = Deal::factory()->create([
            'user_id' => $broker->id,
            'termsheet' => $termsheet->id
        ]);

        $user->storeRelationUserDeal($deal->id, User::LENDER_IGNORE_DEAL);

        $quoteOne = Quote::factory()->create([
            'user_id' => $lender->id,
            'finished' => true,
            'finished_at' => now(),
            'deal_id' => $deal->id,
            'dollar_amount' => 1000000
        ]);

        $quote = Quote::factory()->create([
            'finished' => false,
            'deal_id' => $deal->id,
            'user_id' => $lender->id,
            'dollar_amount' => 25000000
        ]);

        Subscription::factory()->create([
            'stripe_price' => env('STRIPE_SMALL_YEARLY_PRICE_ID'),
            'user_id' => $broker->id
        ]);

        $this->assertTrue($broker->canAccept($deal));
    }

    /**
     * @return void
     * @test
     */
    public function a_user_can_decline_a_quote(): void
    {
        $user = User::factory()->create();
        $broker = User::factory()->create(['role' => 'broker']);
        $lender = User::factory()->create(['role' => 'lender']);

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();

        $deal = Deal::factory()->create([
            'user_id' => $broker->id,
            'dollar_amount' => 25000000
        ]);
        $user->storeRelationUserDeal($deal->id, User::LENDER_IGNORE_DEAL);

        $quote = Quote::factory()->create([
            'finished' => false,
            'deal_id' => $deal->id,
            'user_id' => $lender->id,
            'dollar_amount' => 25000000
        ]);

        Subscription::factory()->create([
            'stripe_price' => env('STRIPE_SMALL_YEARLY_PRICE_ID'),
            'user_id' => $broker->id
        ]);

        $this->actingAs($broker, 'api')
            ->graphQL('
                mutation {
                    setQuoteStatus(
                        input: {
                            id: '. $quote->id.',
                            status: DECLINED
                        }
                    ) {
                        status
                    }
                }
            ')->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'setQuoteStatus' => [
                        'status'
                    ]
                ]
            ]);
    }

    /**
     * @return void
     * @test
     */
    public function a_user_cannot_accept_a_quote_without_the_right_plan(): void
    {
        $user = User::factory()->create();
        $broker = User::factory()->create(['role' => 'broker']);
        $lender = User::factory()->create(['role' => 'lender']);

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();

        $data = handle(new DealData([
            "purchase_loan" => [
                "price" => 10000000,
                "loan_amount" => 25000000,
                "ltc_purchase" => "100.00 %",
                "days_to_close" => null,
                "estimated_value" => 0,
                "estimated_cap_rate" => ""
            ],
        ]));

        $deal = Deal::factory()->create([
            'user_id' => $broker->id,
            'data' => $data
        ]);

        $user->storeRelationUserDeal($deal->id, User::LENDER_IGNORE_DEAL);

        $quote = Quote::factory()->create([
            'finished' => false,
            'deal_id' => $deal->id,
            'user_id' => $lender->id,
            'dollar_amount' => 25000000
        ]);

        Subscription::factory()->create([
            'stripe_price' => env('STRIPE_SMALL_YEARLY_PRICE_ID'),
            'user_id' => $broker->id
        ]);

        $this->actingAs($broker, 'api')
            ->graphQL('
                mutation {
                    setQuoteStatus(
                        input: {
                            id: '. $quote->id.',
                            status: ACCEPTED
                        }
                    ) {
                        status
                    }
                }
            ')->assertOk()
            ->assertGraphQLError(new Error('subscription_upgrade_required'));
    }

    /**
     *@test
     */
    public function a_can_accept_a_purchased_deal_without_a_subscription()
    {
        $user = User::factory()->create();
        $broker = User::factory()->create(['role' => 'broker']);
        $lender = User::factory()->create(['role' => 'lender']);

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();

        $deal = Deal::factory()->purchased()->create([
            'user_id' => $broker->id,
            'dollar_amount' => 25000000
        ]);

        $user->storeRelationUserDeal($deal->id, User::LENDER_IGNORE_DEAL);

        $quote = Quote::factory()->create([
            'finished' => false,
            'deal_id' => $deal->id,
            'user_id' => $lender->id,
            'dollar_amount' => 25000000
        ]);

        Payment::factory()
            ->for($broker, 'user')
            ->for($deal)
            ->paid()
            ->small()
            ->create();

        $this->actingAs($broker, 'api')
            ->graphQL('
                mutation {
                    setQuoteStatus(
                        input: {
                            id: '. $quote->id.',
                            status: ACCEPTED
                        }
                    ) {
                        status
                    }
                }
            ')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'setQuoteStatus' => [
                        'status' => true
                    ]
                ]
            ]);
    }

    /**
     * @return void
     * @test
     */
    public function a_user_need_a_subscription_to_accept_a_deal(): void
    {
        $user = User::factory()->create();
        $broker = User::factory()->create(['role' => 'broker']);
        $lender = User::factory()->create(['role' => 'lender']);

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();

        $deal = Deal::factory()->create([
            'user_id' => $broker->id,
            'dollar_amount' => 25000000
        ]);
        $user->storeRelationUserDeal($deal->id, User::LENDER_IGNORE_DEAL);

        $quote = Quote::factory()->create([
            'finished' => false,
            'deal_id' => $deal->id,
            'user_id' => $lender->id,
            'dollar_amount' => 1000000
        ]);

        $this->actingAs($broker, 'api')
            ->graphQL('
                mutation {
                    setQuoteStatus(
                        input: {
                            id: '. $quote->id.',
                            status: ACCEPTED
                        }
                    ) {
                        status
                    }
                }
            ')->assertOk()
            ->assertGraphQLError(new Error('subscription_required'));
    }

    /**
     * @return void
     * @test
     */
    public function a_user_can_accept_deal_with_right_subscription(): void
    {
        $user = User::factory()->create();
        $broker = User::factory()->create(['role' => 'broker']);
        $lender = User::factory()->create(['role' => 'lender']);

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();

        $deal = Deal::factory()->create([
            'user_id' => $broker->id,
            'dollar_amount' => 800000
        ]);
        $user->storeRelationUserDeal($deal->id, User::LENDER_IGNORE_DEAL);

        $quote = Quote::factory()->create([
            'finished' => false,
            'deal_id' => $deal->id,
            'user_id' => $lender->id,
            'dollar_amount' => 1000000
        ]);

        Subscription::factory()->create([
            'stripe_price' => env('STRIPE_SMALL_YEARLY_PRICE_ID'),
            'user_id' => $broker->id
        ]);

        $this->actingAs($broker, 'api')
            ->graphQL('
                mutation {
                    setQuoteStatus(
                        input: {
                            id: '. $quote->id.',
                            status: ACCEPTED
                        }
                    ) {
                        status
                    }
                }
            ')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'setQuoteStatus' => [
                        'status' => true
                    ]
                ]
            ]);
    }
    protected function createDeal($broker): Deal
    {
        $data = handle(new DealData());
        $deal = new Deal();
        $deal->user_id = $broker->id;
        $deal->data = $data;
        $deal->save();

        return $deal;
    }
}
