<?php

namespace Tests\Unit\GraphQL\Mutations\Payments;

use App\DataTransferObjects\Plan;
use App\Deal;
use App\Quote;
use App\Termsheet;
use App\User;
use Database\Seeders\Termsheets;
use Tests\Mocks\DealData;
use Tests\TestCase;

class OneTimePaymentTest extends TestCase
{
    /**
     * @return void
     * @group payment
     * @test
     */
    public function a_broker_request_checkout_url_for_a_quote(): void
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

        $this->actingAs($broker, 'api')
            ->graphQL('
                mutation {
                    buyQuote(
                        input: {
                            quote_id: '. $quote->id .',
                            success_path: "https://example.com",
                            cancel_path: "https://example.com",
                        }
                    ) {
                        quote_price,
                        checkout_url,
                    }
                }
            ')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'buyQuote' => [
                        'quote_price',
                        'checkout_url'
                    ]
                ]
            ]);
    }

    /**
     * @return void
     * @test
     */
    public function a_broker_request_checkout_url_for_a_deal(): void
    {
        $broker = User::factory()->create(['role' => 'broker']);
        $user = User::factory()->create(['role' => 'lender']);

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();


        $data = handle(new DealData([
            'loan_type' => 1,
            'purchase_loan' => [
                'price' => 10000000,
                'loan_amount' => 10000000,
                'ltc_purchase' => '80.00 %',
                'days_to_close' => null,
                'estimated_value' => 0,
                'estimated_cap_rate' => null,
            ],
            "location" => [
                "street_address" => "East Sunrise Highway",
                "city"=> "New York",
                "sublocality"=> "",
                "state"=> "New York",
                "country"=> "United States",
                "zip_code"=> "11520",
                "place_id"=> "ChIJOwg_06VPwokRYv534QaPC8g",
                "county"=> "Nassau County",
                "street"=> "East Sunrise Highway",
            ],
            "inducted" => [
                "property_type" => [
                    "mixed" => false,
                    "asset_types" => [1,5]
                ],
                'loan_type' => 2
            ]
        ]));

        $deal = Deal::factory()->create([
            'user_id' => $broker->id,
            'finished' => false,
            'finished_at' => null,
            'data' => $data
        ]);

        $user->storeRelationUserDeal($deal->id, User::LENDER_IGNORE_DEAL);

        $this->actingAs($broker, 'api')
            ->graphQL('
                mutation {
                    buyDeal(
                        input: {
                            deal_id: '. $deal->id .',
                            success_path: "https://example.com",
                            cancel_path: "https://example.com",
                        }
                    ) {
                        deal_price,
                        checkout_url,
                    }
                }
            ')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'buyDeal' => [
                        'deal_price',
                        'checkout_url'
                    ]
                ]
            ]);
    }

    /**
     * @return void
     * @test
     */
    public function can_get_right_price_purchase_deal(): void
    {
        $broker = User::factory()->create(['role' => 'broker']);
        $user = User::factory()->create(['role' => 'lender']);

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();


        $data = handle(new DealData([
            'loan_type' => 1,
            'purchase_loan' => [
                'price' => 20000000,
                'loan_amount' => 20000000,
                'ltc_purchase' => '80.00 %',
                'days_to_close' => null,
                'estimated_value' => 0,
                'estimated_cap_rate' => null,
            ],
            "location" => [
                "street_address" => "East Sunrise Highway",
                "city"=> "New York",
                "sublocality"=> "",
                "state"=> "New York",
                "country"=> "United States",
                "zip_code"=> "11520",
                "place_id"=> "ChIJOwg_06VPwokRYv534QaPC8g",
                "county"=> "Nassau County",
                "street"=> "East Sunrise Highway",
            ],
            "inducted" => [
                "property_type" => [
                    "mixed" => false,
                    "asset_types" => [1,5]
                ],
                'loan_type' => 2
            ]
        ]));

        $deal = Deal::factory()->create([
            'user_id' => $broker->id,
            'finished' => false,
            'finished_at' => null,
            'data' => $data
        ]);

        $amount = (new Plan())->getPrice($deal);
        $this->assertEquals(750, $amount);
    }
}
