<?php

namespace Tests\Unit\GraphQL\Mutations\Deal;

use App\AssetTypes;
use App\Broker;
use App\Company;
use App\Deal;
use App\DealEmail;
use App\Enums\DealPurchaseType;
use App\Events\DealPublished;
use App\Jobs\UpdateLimitedDeals;
use App\Lender;
use App\Listeners\NotifyPublishedDeal;
use App\Notifications\DealCreated;
use App\Quote;
use App\Termsheet;
use App\User;
use App\UserDeals;
use App\Jobs\DealNotifyInterestedLenders;
use Carbon\Carbon;
use Database\Seeders\Termsheets;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Laravel\Cashier\Subscription;
use Tests\Mocks\DealData;
use Tests\Mocks\Mutations\PublishDealMutation;
use Tests\TestCase;

class StoreDealTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testCheckDealFlow()
    {
        $broker = Broker::factory()->create();
        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();
        $deal = Deal::factory()->create(['user_id' => $broker->id, 'data' => [
            'loan_type' => 1,
        ]]);

        $args = [
            'id' => $deal->id,
            'step' => 'dealLoanType',
            'loan_type' => 3,
        ];

        $dataType = $deal->checkDealFlow($deal->id, $args);

        $this->assertTrue($dataType === 'loan_type');
    }

    public function testResetData()
    {
        $broker = Broker::factory()->create();
        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();
        $deal = Deal::factory()->create(['user_id' => $broker->id, 'data' => [
            'loan_type' => 1,
            'purchase_loan' => [
                'price' => 100000,
                'loan_amount' => 80000,
                'ltc_purchase' => '80.00 %',
                'days_to_close' => null,
                'estimated_value' => 0,
                'estimated_cap_rate' => null,
            ],
        ]]);

        $dataType = 'loan_type';

        $mapper = new \App\DataTransferObjects\DealMapper($deal->id);
        $dealReset = $mapper->resetData($dataType);
        $this->assertTrue($dealReset->data['purchase_loan']['price'] === 0);
    }

    /** @test */
    public function it_can_create_a_deal_interesting_for_a_lender()
    {
        Notification::fake();
        Event::fake(DealPublished::class);

        $company = Company::factory()->create([
            'is_approved' => true,
            'company_status' => 1,
        ]);

        $broker = Broker::factory()->create([
            'company_id' => $company->id,
        ]);

        $lender = Lender::factory()->create([
            'role' => 'lender',
            'beta_user' => true,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'something@example.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'referrer_id' => null,
            'company_id' => $company->id,
            'metas' => [
                'perfect_fit' => [
                    'areas' => [
                        [
                            'area' => [
                                'place_id' => 'ChIJOwg_06VPwokRYv534QaPC8g',
                                'long_name' => 'New York',
                                'formatted_address' => 'New York, NY, USA',
                                'state' => 'New York',
                                'county' => '',
                                'country' => 'United States',
                                'zip_code' => '',
                                'city' => 'New York',
                                'sublocality' => '',
                            ],
                            'exclusions' => [],
                        ],
                        [
                            'area' => [
                                'place_id' => 'ChIJvypWkWV2wYgR0E7HW9MTLvc',
                                'long_name' => '',
                                'formatted_address' => 'Florida, USA',
                                'state' => 'Florida',
                                'county' => '',
                                'country' => 'United States',
                                'zip_code' => '',
                                'sublocality' => '',
                                'city' => '',
                            ],
                            'exclusions' => [],
                        ],
                        [
                            'area' => [
                                'place_id' => 'ChIJSTKCCzZwQIYRPN4IGI8c6xY',
                                'long_name' => '',
                                'formatted_address' => 'Texas, USA',
                                'state' => 'Texas',
                                'county' => '',
                                'country' => 'United States',
                                'zip_code' => '',
                                'sublocality' => '',
                                'city' => '',
                            ],
                            'exclusions' => [],
                        ],
                    ],
                    'loan_size' => [
                        'min' => 5000000,
                        'max' => 15000000,
                    ],
                    'asset_types' => [5],
                    'multifamily' => null,
                ],
            ],
        ]);

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();

        AssetTypes::factory()
            ->create([
                'id' => 5,
                'title' => 'Construction',
            ]);
        AssetTypes::factory()
            ->create([
                'id' => 1,
                'title' => 'Retail',
            ]);

        $this->actingAs($broker, 'api')
            ->graphQL('
                mutation {
                    deal(
                        input: {
                                finished: false,
                                finishApproved: false,
                                updated_at: "' . Carbon::now()->toDateString() . '",
                                step: "initial",
                                location: {
                                    street_address: "East Sunrise Highway",
                                    city: "New York",
                                    sublocality: "",
                                    state: "New York",
                                    country: "United States",
                                    zip_code: "11520",
                                    place_id: "ChIJOwg_06VPwokRYv534QaPC8g",
                                    county: "Nassau County",
                                    street: "East Sunrise Highway",
                                },
                                loan_type: CONSTRUCTION,
                                property_type: CONSTRUCTION,
                                investment_details: {
                                    propType: RETAIL
                                }
                                construction_loan: {
                                    buying_land: "",
                                    debt_on_property: "",
                                    purchase_price: "",
                                    purchase_date: "",
                                    debt_amount: "",
                                    lender_name: "",
                                    loanAmount: 9000000,
                                    show_address_construction: "",
                                    floors: "2"
                                }
                        }
                    ) {
                        id,
                    }
                }
            ')
            ->assertOk();

        Event::assertNotDispatched(DealPublished::class);

        $deal = $broker->deals->last();
        $this->assertCount(
            0,
            UserDeals::where('user_id', $lender->id)->where('deal_id', $deal->id)->get()
        );
        $this->assertCount(
            0,
            DealEmail::where('email', $lender->email)->where('deal_id', $deal->id)->get()
        );

        Subscription::factory()->create([
            'stripe_price' => env('STRIPE_EXTRA_LARGE_YEARLY_PRICE_ID'),
            'user_id' => $broker->id
        ]);

        $this->actingAs($broker, 'api')
            ->graphQL('
                mutation {
                    deal(
                        input: {
                                id: ' . $deal->id . ',
                                finished: true,
                                finishApproved: true,
                                updated_at: "' . Carbon::now()->toDateString() . '",
                                finished_at: "' . Carbon::now()->toDateString() . '",
                                step: "initial",
                                location: {
                                    street_address: "East Sunrise Highway",
                                    city: "New York",
                                    sublocality: "",
                                    state: "New York",
                                    country: "United States",
                                    zip_code: "11520",
                                    place_id: "ChIJOwg_06VPwokRYv534QaPC8g",
                                    county: "Nassau County",
                                    street: "East Sunrise Highway",
                                },
                                loan_type: CONSTRUCTION,
                                property_type: CONSTRUCTION,
                                investment_details: {
                                    propType: RETAIL
                                }
                                construction_loan: {
                                    buying_land: "",
                                    debt_on_property: "",
                                    purchase_price: "",
                                    purchase_date: "",
                                    debt_amount: "",
                                    lender_name: "",
                                    loanAmount: 9000000,
                                    show_address_construction: "",
                                    floors: "2"
                                }
                        }
                    ) {
                        id,
                    }
                }
            ')
            ->assertOk();

        Event::assertDispatched(DealPublished::class, fn ($dealPublished) => $deal->is($dealPublished->deal));

        $dealPublished = new DealPublished($deal);
        (new NotifyPublishedDeal())->handle($dealPublished);

        $this->assertCount(
            1,
            DealEmail::where('email', $lender->email)->where('deal_id', $deal->id)->get()
        );

        Notification::assertSentTo($lender, function (DealCreated $notification) {
            $this->assertEquals(DealCreated::class, $notification::class);
            return true;
        });
    }

    /**
     * @return void
     * @test
     */
    public function user_can_publish_a_deal(): void
    {
        $company = Company::factory()->create([
            'company_status' => 1
        ]);

        $user = User::factory()->create();
        $broker = User::factory()->create([
            'role' => 'broker',
            'company_id' => $company->id,
        ]);

        $lender = User::factory()->create(['role' => 'lender']);

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();

        $data = handle(new DealData([
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
            'dollar_amount' => 800000,
            'finished' => false,
            'finished_at' => null,
            'data' => $data
        ]);
        $user->storeRelationUserDeal($deal->id, User::LENDER_IGNORE_DEAL);

        AssetTypes::factory()
            ->create([
                'id' => 4,
                'title' => 'Construction',
            ]);

        $quote = Quote::factory()->create([
            'finished' => false,
            'deal_id' => $deal->id,
            'user_id' => $lender->id,
            'dollar_amount' => 1000000
        ]);

        Subscription::factory()->create([
            'stripe_price' => env('STRIPE_EXTRA_LARGE_PRICE_ID'),
            'user_id' => $broker->id
        ]);

        $this->actingAs($broker, 'api')
            ->graphQL('
                mutation {
                    deal(
                        input: {
                            id: '. $deal->id.',
                            finished: true,
                            sensitivity: {
                                fees: 0,
                                leverage: 0,
                                interestOnlyPeriod: 1,
                                nonPrepaymentPenalty:3
                            }
                        }
                    ) {
                        id,
                        finished
                        sensitivity {
                            interestOnlyPeriod
                            nonPrepaymentPenalty
                        }
                    }
                }
            ')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'deal' => [
                        'id' => $deal->id,
                        'finished' => true,
                        'sensitivity' => [
                            'interestOnlyPeriod' => 1,
                            'nonPrepaymentPenalty' => 3
                        ]
                    ]
                ]
            ]);
    }

    /**
     * @return void
     * @test
     */
    public function user_needs_a_plan_to_publish_deal(): void
    {
        $company = Company::factory()->create([
            'company_status' => 1
        ]);

        $user = User::factory()->create();

        $broker = User::factory()->create([
            'role' => 'broker',
            'company_id' => $company->id,
        ]);

        $lender = User::factory()->create(['role' => 'lender']);

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();

        $data = handle(new DealData([
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
            'dollar_amount' => 800000,
            'finished' => false,
            'finished_at' => null,
            'data' => $data
        ]);
        $user->storeRelationUserDeal($deal->id, User::LENDER_IGNORE_DEAL);

        AssetTypes::factory()
            ->create([
                'id' => 4,
                'title' => 'Construction',
            ]);

        $quote = Quote::factory()->create([
            'finished' => false,
            'deal_id' => $deal->id,
            'user_id' => $lender->id,
            'dollar_amount' => 1000000
        ]);

        $this->actingAs($broker, 'api')
            ->graphQL('
                mutation {
                    deal(
                        input: {
                            id: '. $deal->id.',
                            finished: true
                        }
                    ) {
                        id,
                        finished
                    }
                }
            ')
            ->assertOk()
            ->assertGraphQLError(new Error('subscription_required'));
    }

    /**
     * @return void
     * @test
     */
    public function a_user_can_publish_a_deal_without_a_subscription()
    {
        $company = Company::factory()->create([
            'company_status' => 1
        ]);

        $user = User::factory()->create();
        $broker = User::factory()->create([
            'role' => 'broker',
            'company_id' => $company->id,
        ]);

        $lender = User::factory()->create(['role' => 'lender']);

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();

        $data = handle(new DealData([
            'purchase_loan' => [
                'price' => 10000000,
                'loan_amount' => 10000000,
                'ltc_purchase' => '80.00 %',
                'days_to_close' => null,
                'estimated_value' => 0,
                'estimated_cap_rate' => null,
            ],
            'property_type' => 1,
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
            'dollar_amount' => 800000,
            'finished' => false,
            'finished_at' => null,
            'data' => $data
        ]);

        $user->storeRelationUserDeal($deal->id, User::LENDER_IGNORE_DEAL);

        Subscription::factory()->create([
            'stripe_price' => env('STRIPE_SMALL_PRICE_ID'),
            'user_id' => $broker->id,
            'stripe_status' => 'active'
        ]);

        AssetTypes::factory()
            ->create([
                'id' => 4,
                'title' => 'Construction',
            ]);

        Quote::factory()->create([
            'finished' => false,
            'deal_id' => $deal->id,
            'user_id' => $lender->id,
            'dollar_amount' => null
        ]);

        $this->actingAs($broker, 'api')
            ->graphQL('
                mutation {
                    deal(
                        input: {
                            id: '. $deal->id.',
                            finished: true,
                            force: true
                        }
                    ) {
                        id,
                        finished,
                        deal_type
                    }
                }
            ')
            ->assertOk()
            ->assertJson([
                'data' => [
                    'deal' => [
                        'id' => $deal->id,
                        'finished' => true
                    ]
                ]
            ]);

        $this->assertEquals('Limited Deal', $deal->getDealType());
    }

    /**
     * @test
     */
    public function when_a_subscribing_broker_publishes_a_deal_we_mark_it_premium(): void
    {
        // Skip dispatching the job to notify lenders
        Queue::fake([
            DealNotifyInterestedLenders::class,
        ]);

        $broker = Broker::factory()->withCompany()->create();

        $this->setupTermSheets();

        $deal = Deal::factory()
                    ->for($broker)
                    ->pricedAt(200000)
                    ->notPublished()
                    ->create();

        $subscription = Subscription::factory()->for($broker, 'owner')->create([
            'stripe_price' => config('plans.medium.price_id'),
        ]);

        // Deal Is not Premium Before Publishing
        $this->assertNull($deal->premiumed_at);
        $this->assertFalse($deal->isPremium());

        $this->actingAs($broker, 'api')
            ->graphQL(new PublishDealMutation($deal))
            ->assertOk();

        $this->assertNotNull($deal->fresh()->premiumed_at);
        $this->assertTrue($deal->fresh()->isPremium());
    }

    /**
     * @return void
     * @test
     */
    public function user_needs_the_right_plan_to_publish_deal(): void
    {
        $company = Company::factory()->create([
            'company_status' => 1
        ]);

        $user = User::factory()->create();
        $broker = User::factory()->create([
            'role' => 'broker',
            'company_id' => $company->id,
        ]);

        $lender = User::factory()->create(['role' => 'lender']);

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();

        $data = handle(new DealData([
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
            'dollar_amount' => 10000000,
            'finished' => false,
            'finished_at' => null,
            'data' => $data
        ]);
        $user->storeRelationUserDeal($deal->id, User::LENDER_IGNORE_DEAL);

        AssetTypes::factory()
            ->create([
                'id' => 4,
                'title' => 'Construction',
            ]);

        Subscription::factory()->create([
            'stripe_price' => env('STRIPE_MEDIUM_PRICE_ID'),
            'user_id' => $broker->id
        ]);

        $this->actingAs($broker, 'api')
            ->graphQL('
                mutation {
                    deal(
                        input: {
                            id: '. $deal->id.',
                            finished: true
                            force: false
                        }
                    ) {
                        id,
                        finished
                    }
                }
            ')
            ->assertOk()
            ->assertGraphQLError(new Error('subscription_upgrade_required'));
    }

    /**
     * @return void
     * @test
     */
    public function user_limited_deals_are_updated_when_user_subs_to_plan(): void
    {
        $company = Company::factory()->create([
            'company_status' => 1
        ]);

        $broker = User::factory()->create([
            'role' => 'broker',
            'company_id' => $company->id,
        ]);

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();


        $deal = Deal::factory()
            ->purchased(DealPurchaseType::NOT_PURCHASED)
            ->pricedAt(50000000)
            ->finished()
            ->create(['user_id' => $broker->id]);

        $broker->storeRelationUserDeal($deal->id, User::LENDER_IGNORE_DEAL);

        Subscription::factory()->create([
            'stripe_price' => env('STRIPE_EXTRA_LARGE_PRICE_ID'),
            'user_id' => $broker->id
        ]);

        UpdateLimitedDeals::dispatch($broker);

        $this->assertEquals(DealPurchaseType::PURCHASED_VIA_SUBSCRIPTION, $deal->refresh()->purchase_type);
    }
}
