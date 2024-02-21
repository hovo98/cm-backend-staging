<?php

namespace Tests\Feature;

use App\Events\DealPublished;
use App\Events\DealPurchased;
use App\Jobs\DealNotifyInterestedLenders;
use App\AssetTypes;
use App\Broker;
use App\Company;
use App\Deal;
use App\DealAssetType;
use App\Payment;
use App\Quote;
use App\Termsheet;
use App\User;
use App\UserDeals;
use Database\Seeders\Termsheets;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Laravel\Cashier\Subscription;
use Tests\Mocks\DealData;
use Tests\TestCase;

class FilterDealsTest extends TestCase
{
    /**
     * @return void
     * @test
     * @skip
     */
    public function it_can_filter_deals_with_quote_limit_reached()
    {
        $this->markTestSkipped();
        $company = Company::factory()->create([
            'is_approved' => true,
            'company_status' => 1,
        ]);

        $broker = Broker::factory()->create([
            'company_id' => $company->id,
        ]);

        $lender = User::factory()->create([
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
                    'asset_types' => [5, 1],
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

        $deal =  Deal::factory()
            ->for($broker)
            ->withData($data)
            ->finished()
            ->create(['dollar_amount' => 10000000]);

        Quote::factory()->count(3)->create([
            'finished' => true,
            'finished_at' => now(),
            'deal_id' => $deal->id,
            'user_id' => $lender->id,
        ]);

        $deal2 =  Deal::factory()
            ->for($broker)
            ->withData($data)
            ->finished()
            ->create([
                'dollar_amount' => 10000000,
                'quote_limit_reached' => true,
            ]);

        UserDeals::create([
            'user_id' => $lender->id,
            'deal_id' => $deal->id,
            'relation_type' => 1
        ]);

        UserDeals::create([
            'user_id' => $lender->id,
            'deal_id' => $deal2->id,
            'relation_type' => 1
        ]);

        DealAssetType::factory()->create([
            'deal_id' => $deal->id,
            'asset_type_id' => 1
        ]);

        DealAssetType::factory()->create([
            'deal_id' => $deal->id,
            'asset_type_id' => 5
        ]);

        DealAssetType::factory()->create([
            'deal_id' => $deal2->id,
            'asset_type_id' => 1
        ]);

        DealAssetType::factory()->create([
            'deal_id' => $deal2->id,
            'asset_type_id' => 5
        ]);

        $this->actingAs($lender, 'api')
            ->graphQL('
                query {
                    dealsFilter (
                        pagination: {
                            page: 1,
                            perPage: 10
                        },
                       input : {
                            context: GENERAL,
                            filterName: PERFECT_FIT,
                            searchTerms: "",
                            loanSize: {
                                min: 0,
                                max: 10000000
                            },
                            assetTypes: RETAIL,
                            sortBy: {
                                sort: GENERAL,
                                by: DESC
                            }
                       }
                    ) {
                        data {
                            id,
                            quotes {
                                id,
                                dealID
                            }
                        }
                    }
                }
            ')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'dealsFilter' => [
                        'data'
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.dealsFilter.data');
    }

    /**
     * @return void
     * @test
     */
    public function it_can_filter_deals_showing_deals_with_quotes_from_lender()
    {
        $company = Company::factory()->create([
            'is_approved' => true,
            'company_status' => 1,
        ]);

        $broker = Broker::factory()->create([
            'company_id' => $company->id,
        ]);

        $lender = User::factory()->create([
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
                    'asset_types' => [5, 1],
                    'multifamily' => null,
                ],
            ],
        ]);

        $lender2 = User::factory()->create([
            'role' => 'lender',
            'beta_user' => true,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'something2@example.com',
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
                    'asset_types' => [5, 1],
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

        $deal = Deal::factory()
            ->for($broker)
            ->finished()
            ->withData($data)
            ->create([
                'dollar_amount' => 10000000,
                'quote_limit_reached' => true,
            ]);

        $deal2 = Deal::factory()
            ->for($broker)
            ->finished()
            ->withData($data)
            ->create([
                'dollar_amount' => 10000000,
                'quote_limit_reached' => false,
            ]);


        Quote::factory()
            ->for($lender2, 'lender')
            ->for($deal)->create([
                'finished' => true,
                'finished_at' => now(),
            ]);

        UserDeals::create([
            'user_id' => $lender->id,
            'deal_id' => $deal->id,
            'relation_type' => 1
        ]);

        UserDeals::create([
            'user_id' => $lender->id,
            'deal_id' => $deal2->id,
            'relation_type' => 1
        ]);

        DealAssetType::factory()->create([
            'deal_id' => $deal->id,
            'asset_type_id' => 1
        ]);

        DealAssetType::factory()->create([
            'deal_id' => $deal->id,
            'asset_type_id' => 5
        ]);

        DealAssetType::factory()->create([
            'deal_id' => $deal2->id,
            'asset_type_id' => 1
        ]);

        DealAssetType::factory()->create([
            'deal_id' => $deal2->id,
            'asset_type_id' => 5
        ]);

        $this->actingAs($broker, 'api')
            ->graphQL('
                query {
                    dealsFilter (
                        pagination: {
                            page: 1,
                            perPage: 10
                        },
                       input : {
                            context: GENERAL,
                            filterName: PERFECT_FIT,
                            searchTerms: "",
                            loanSize: {
                                min: 0,
                                max: 10000000
                            },
                            assetTypes: RETAIL,
                            sortBy: {
                                sort: GENERAL,
                                by: DESC
                            }
                       }
                    ) {
                        data {
                            id,
                            is_premium
                            deal_type
                            quotes {
                                dealID,
                            }
                        }
                    }
                }
            ')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'dealsFilter' => [
                        'data' => [
                            '*' => [
                                'id',
                                'is_premium',
                                'deal_type',
                                'quotes'

                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.dealsFilter.data');
    }

    /**
     * @return void
     * @test
     */
    public function it_can_filter_quotes_for_a_broker()
    {
        $company = Company::factory()->create([
            'is_approved' => true,
            'company_status' => 1,
        ]);

        $broker = Broker::factory()->create([
            'company_id' => $company->id,
        ]);

        $lender = User::factory()->create([
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
                    'asset_types' => [5, 1],
                    'multifamily' => null,
                ],
            ],
        ]);

        $lender2 = User::factory()->create([
            'role' => 'lender',
            'beta_user' => true,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'something2@example.com',
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
                    'asset_types' => [5, 1],
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

        $deal = Deal::factory()
            ->for($broker)
            ->finished()
            ->withData($data)
            ->create([
                'dollar_amount' => 10000000,
                'quote_limit_reached' => true,
            ]);

        $deal2 = Deal::factory()
            ->for($broker)
            ->finished()
            ->withData($data)
            ->create([
                'dollar_amount' => 10000000,
                'quote_limit_reached' => false,
            ]);

        Payment::factory()->create([
            'deal_id' => $deal2->id,
            'user_id' => $broker->id,
            'payment_status' => Payment::STATUS_PAID
        ]);

        Quote::factory()
            ->for($lender2, 'lender')
            ->for($deal)->create([
                'finished' => true,
                'finished_at' => now(),
            ]);

        UserDeals::create([
            'user_id' => $lender->id,
            'deal_id' => $deal->id,
            'relation_type' => 1
        ]);

        UserDeals::create([
            'user_id' => $lender->id,
            'deal_id' => $deal2->id,
            'relation_type' => 1
        ]);

        DealAssetType::factory()->create([
            'deal_id' => $deal->id,
            'asset_type_id' => 1
        ]);

        DealAssetType::factory()->create([
            'deal_id' => $deal->id,
            'asset_type_id' => 5
        ]);

        DealAssetType::factory()->create([
            'deal_id' => $deal2->id,
            'asset_type_id' => 1
        ]);

        DealAssetType::factory()->create([
            'deal_id' => $deal2->id,
            'asset_type_id' => 5
        ]);

        $this->actingAs($broker, 'api')
            ->graphQL('
                query {
                    dealsFilter (
                        pagination: {
                            page: 1,
                            perPage: 10
                        },
                       input : {
                            context: GENERAL,
                            filterName: PERFECT_FIT,
                            searchTerms: "",
                            loanSize: {
                                min: 0,
                                max: 10000000
                            },
                            assetTypes: RETAIL,
                            sortBy: {
                                sort: GENERAL,
                                by: DESC
                            }
                       }
                    ) {
                        data {
                            id,
                            is_premium
                            quotes {
                                dealID,
                            }
                        }
                    }
                }
            ')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'dealsFilter' => [
                        'data' => [
                            '*' => [
                                'id',
                                'is_premium',
                                'quotes'
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(2, 'data.dealsFilter.data');
    }

    /**
     * @return void
     * @test
     */
    public function deal_premium_based_on_one_time_payment(): void
    {
        $company = Company::factory()->create([
            'is_approved' => true,
            'company_status' => 1,
        ]);

        $broker = Broker::factory()->create([
            'company_id' => $company->id,
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

        $deal = Deal::factory()
            ->for($broker)
            ->finished()
            ->withData($data)
            ->create([
                'dollar_amount' => 10000000,
                'quote_limit_reached' => true,
            ]);

        $this->actingAs($broker, 'api');

        $payment = Payment::factory()
            ->large()
            ->for($deal)
            ->for($broker, 'user')
            ->paid()
            ->create();

        DealPurchased::dispatch($payment);

        $this->assertTrue($deal->fresh()->isPremium());
    }

    /**
     * @return void
     * @test
     */
    public function deal_premium_based_on_subscription(): void
    {
        Queue::fake([
            DealNotifyInterestedLenders::class,
        ]);

        $broker = Broker::factory()->withCompany()->create();

        $this->setupTermSheets();

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

        $deal = Deal::factory()
            ->for($broker)
            ->finished()
            ->withData($data)
            ->create([
                'dollar_amount' => 10000000,
                'quote_limit_reached' => true,
            ]);

        Subscription::factory()->for($broker, 'owner')
            ->create([
                'stripe_price' => config('plans.extra-large.price_id'),
            ]);

        DealPublished::dispatch($deal);

        $this->assertTrue($deal->isPremium());
    }
}
