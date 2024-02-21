<?php

declare(strict_types=1);

namespace Tests\Services\QueryServices\Lender\Deals;

use App\Broker;
use App\Deal;
use App\Services\QueryServices\Lender\Deals\SortByDeals;
use App\Termsheet;
use Database\Seeders\Termsheets;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SortByDealsTest extends TestCase
{
    /**
     * @var SortByDeals
     */
    private $service;

    /**
     * @var Builder
     */
    private $queryService;

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $broker = Broker::factory()->create(['referrer_id' => null]);
        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();
        $this->queryService = DB::table('deals')->select('id');

        // Construction -Retail
        $deal = Deal::factory()->create(['id' => 1, 'user_id' => $broker->id,
            'updated_at' => '2021-01-28 13:11:50',
            'finished_at' => null,
            'location' => null,
            'dollar_amount' => null,
            'main_type' => null,
            'data' => [
                'inducted' => [
                    'loan_type' => 3,
                    'loan_amount' => 70,
                    'property_type' => [
                        'type' => 3,
                        'mixed' => false,
                        'asset_types' => [1, 5],
                        'main_type' => 1,
                    ],
                ],
            ],
        ]);
        // Owner occupied - Refinance
        $deal1 = Deal::factory()->create(['id' => 2, 'user_id' => $broker->id,
            'updated_at' => '2020-12-14 12:29:19',
            'finished_at' => '2020-12-14 12:29:19',
            'location' => 'texas dallas dallas rockaway boulevard 1125',
            'dollar_amount' => 40,
            'main_type' => 6,
            'data' => [
                'inducted' => [
                    'loan_type' => 2,
                    'loan_amount' => 40,
                    'property_type' => [
                        'type' => 2,
                        'mixed' => false,
                        'asset_types' => [6],
                        'main_type' => 6,
                    ],
                ],
            ],
        ]);
        // Investment - Refinance
        $deal2 = Deal::factory()->create(['id' => 3, 'user_id' => $broker->id,
            'updated_at' => '2020-12-14 12:29:19',
            'finished_at' => '2020-12-14 12:29:19',
            'location' => 'new york new york the bronx rockaway boulevard 1125',
            'dollar_amount' => 50,
            'main_type' => 2,
            'data' => [
                'inducted' => [
                    'loan_type' => 2,
                    'loan_amount' => 50,
                    'property_type' => [
                        'type' => 1,
                        'mixed' => false,
                        'asset_types' => [2],
                        'main_type' => 2,
                    ],
                ],
            ],
        ]);
        // Investment - Purchase
        $deal3 = Deal::factory()->create(['id' => 4, 'user_id' => $broker->id,
            'updated_at' => '2021-01-28 13:11:50',
            'finished_at' => '2021-01-28 13:11:50',
            'location' => 'texas houston houston rockaway boulevard 1125',
            'dollar_amount' => 90,
            'main_type' => 2,
            'data' => [
                'inducted' => [
                    'loan_type' => 1,
                    'loan_amount' => 90,
                    'property_type' => [
                        'type' => 1,
                        'mixed' => false,
                        'asset_types' => [2],
                        'main_type' => 2,
                    ],
                ],
            ],
        ]);
        // Construction - Purchase
        $deal4 = Deal::factory()->create(['id' => 5, 'user_id' => $broker->id,
            'updated_at' => '2021-01-28 13:11:50',
            'finished_at' => '2021-01-28 13:11:50',
            'location' => 'florida orlando orlando rockaway boulevard 11225',
            'dollar_amount' => 80,
            'main_type' => 5,
            'data' => [
                'inducted' => [
                    'loan_type' => 1,
                    'loan_amount' => 80,
                    'property_type' => [
                        'type' => 3,
                        'mixed' => false,
                        'asset_types' => [2, 5],
                        'main_type' => 2,
                    ],
                ],
            ],
        ]);
        // Investment - Refinance
        $deal5 = Deal::factory()->create(['id' => 6, 'user_id' => $broker->id,
            'updated_at' => '2020-12-14 12:29:19',
            'finished_at' => '2020-12-14 12:29:19',
            'location' => 'new york new york the bronx rockaway boulevard 1125',
            'dollar_amount' => 50,
            'main_type' => 1,
            'data' => [
                'inducted' => [
                    'loan_type' => 2,
                    'loan_amount' => 50,
                    'property_type' => [
                        'type' => 1,
                        'mixed' => false,
                        'asset_types' => [1],
                        'main_type' => 2,
                    ],
                ],
            ],
        ]);
        // Owner occupied - Purchase
        $deal6 = Deal::factory()->create(['id' => 7, 'user_id' => $broker->id,
            'updated_at' => '2020-12-14 12:29:19',
            'finished_at' => '2020-12-14 12:29:19',
            'location' => 'new york new york queens rockaway boulevard 1125',
            'dollar_amount' => 80,
            'main_type' => 6,
            'data' => [
                'inducted' => [
                    'loan_type' => 1,
                    'loan_amount' => 80,
                    'property_type' => [
                        'type' => 2,
                        'mixed' => false,
                        'asset_types' => [6],
                        'main_type' => 6,
                    ],
                ],
            ],
        ]);

        $this->service = $this->app->make(SortByDeals::class);
    }

    /**
     * Test if deals are sorted by location
     */
    public function testSortByLocation()
    {
        $args['sortBy'] = [
            'sort' => 'location',
            'by' => 'DESC',
        ];
        $args['query'] = $this->queryService;
        $sortDeals = $this->service->run($args);
        $isSort = false;

        foreach ($sortDeals as $key => $sortDeal) {
            if ($key === 0 && $sortDeal->id === 4) {
                $isSort = true;
            }
            if ($key === 6 && $sortDeal->id === 5) {
                $isSort = true;
            }
        }

        $this->assertTrue($isSort);
    }

    /**
     * Test if deals are sorted by date posted
     */
    public function testSortByDatePosted()
    {
        $args['sortBy'] = [
            'sort' => 'date_posted',
            'by' => 'DESC',
        ];
        $args['query'] = $this->queryService;
        $sortDeals = $this->service->run($args);
        $isSort = false;

        foreach ($sortDeals as $key => $sortDeal) {
            if ($key === 0 && $sortDeal->id === 1) {
                $isSort = true;
            }
            if ($key === 6 && $sortDeal->id === 3) {
                $isSort = true;
            }
        }

        $this->assertTrue($isSort);
    }

    /**
     * Test if deals are sorted by property type
     */
    public function testSortByPropertyType()
    {
        $args['sortBy'] = [
            'sort' => 'property_type',
            'by' => 'DESC',
        ];
        $args['query'] = $this->queryService;
        $sortDeals = $this->service->run($args);
        $isSort = false;

        foreach ($sortDeals as $key => $sortDeal) {
            if ($key === 0 && $sortDeal->id === 2) {
                $isSort = true;
            }
            if ($key === 6 && $sortDeal->id === 6) {
                $isSort = true;
            }
        }
        $this->assertTrue($isSort);
    }

    /**
     * Test if deals are sorted by loan amount
     */
    public function testSortByLoanAmount()
    {
        $args['sortBy'] = [
            'sort' => 'loan_amount',
            'by' => 'DESC',
        ];
        $args['query'] = $this->queryService;
        $sortDeals = $this->service->run($args);
        $isSort = false;

        foreach ($sortDeals as $key => $sortDeal) {
            if ($key === 0 && $sortDeal->id === 4) {
                $isSort = true;
            }
            if ($key === 6 && $sortDeal->id === 2) {
                $isSort = true;
            }
        }
        $this->assertTrue($isSort);
    }

    /**
     * Test if deals are sorted by Asset Type
     */
    public function testSortByLoanType()
    {
        $args['sortBy'] = [
            'sort' => 'loan_type',
            'by' => 'DESC',
        ];
        $args['query'] = $this->queryService;
        $sortDeals = $this->service->run($args);
        $isSort = false;

        foreach ($sortDeals as $key => $sortDeal) {
            if ($key === 0 && $sortDeal->id === 1) {
                $isSort = true;
            }
            if ($key === 6 && $sortDeal->id === 7) {
                $isSort = true;
            }
        }
        $this->assertTrue($isSort);
    }
}
