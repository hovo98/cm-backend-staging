<?php

declare(strict_types=1);

namespace Tests\Services\QueryServices\Lender\Deals;

use App\AssetTypes;
use App\Broker;
use App\Deal;
use App\DealAssetType;
use App\Lender;
use App\Services\QueryServices\Lender\Deals\FilterByAssetType;
use App\Termsheet;
use Database\Seeders\Termsheets;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class FilterByAssetTypeTest
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class FilterByAssetTypeTest extends TestCase
{
    /**
     * @var FilterByAssetType
     */
    private $serviceFilterByAsssetType;

    /**
     * @var Builder
     */
    private $queryService;

    /**
     * @var Lender
     */
    private $lender;

    /**
     * @var Lender
     */
    private $lender1;

    /**
     * @var Lender
     */
    private $lender2;

    /**
     * @var Lender
     */
    private $lender3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();
        $this->serviceFilterByAsssetType = $this->app->make(FilterByAssetType::class);
        $this->queryService = DB::table('deals')->whereNull('deleted_at')
            ->where('finished', '=', true)->select('deals.id')
            ->join('deal_asset_type', 'deals.id', '=', 'deal_asset_type.deal_id');

        $this->lender = Lender::factory()->create([
            'referrer_id' => null,
            'metas' => [
                'perfect_fit' => [
                    'areas' => [
                        [
                            'area' => [
                                'place_id' => 'ChIJOwg_06VPwokRYv534QaPC8g',
                                'long_name' => 'New York',
                                'formatted_address' => 'New York, NY, USA',
                            ],
                            'exclusions' => [
                                [
                                    'place_id' => 'ChIJsXxpOlWLwokRd1zxj6dDblU',
                                    'long_name' => 'The Bronx',
                                    'formatted_address' => 'The Bronx, NY, USA',
                                ],
                                [
                                    'place_id' => 'ChIJCSF8lBZEwokRhngABHRcdoI',
                                    'long_name' => 'Brooklyn',
                                    'formatted_address' => 'Brooklyn, NY, USA',
                                ],
                            ],
                        ],
                        [
                            'area' => [
                                'place_id' => 'ChIJvypWkWV2wYgR0E7HW9MTLvc',
                                'long_name' => 'Florida',
                                'formatted_address' => 'Florida, USA',
                            ],
                            'exclusions' => [],
                        ],
                        [
                            'area' => [
                                'place_id' => 'ChIJSTKCCzZwQIYRPN4IGI8c6xY',
                                'long_name' => 'Texas',
                                'formatted_address' => 'Texas, USA',
                            ],
                            'exclusions' => [
                                [
                                    'place_id' => 'ChIJAYWNSLS4QIYROwVl894CDco',
                                    'long_name' => 'Houston',
                                    'formatted_address' => 'Houston, TX, USA',
                                ],
                            ],
                        ],
                    ],
                    'loan_size' => [
                        'max' => 15000000,
                        'min' => 5000000,
                    ],
                    'asset_types' => [5, 6],
                    'multifamily' => null,
                ],
            ],
        ]);

        $this->lender1 = Lender::factory()->create([
            'referrer_id' => null,
            'metas' => [
                'perfect_fit' => [
                    'areas' => [
                        [
                            'area' => [
                                'place_id' => 'ChIJOwg_06VPwokRYv534QaPC8g',
                                'long_name' => 'New York',
                                'formatted_address' => 'New York, NY, USA',
                            ],
                            'exclusions' => [
                                [
                                    'place_id' => 'ChIJsXxpOlWLwokRd1zxj6dDblU',
                                    'long_name' => 'The Bronx',
                                    'formatted_address' => 'The Bronx, NY, USA',
                                ],
                                [
                                    'place_id' => 'ChIJCSF8lBZEwokRhngABHRcdoI',
                                    'long_name' => 'Brooklyn',
                                    'formatted_address' => 'Brooklyn, NY, USA',
                                ],
                            ],
                        ],
                        [
                            'area' => [
                                'place_id' => 'ChIJvypWkWV2wYgR0E7HW9MTLvc',
                                'long_name' => 'Florida',
                                'formatted_address' => 'Florida, USA',
                            ],
                            'exclusions' => [],
                        ],
                        [
                            'area' => [
                                'place_id' => 'ChIJSTKCCzZwQIYRPN4IGI8c6xY',
                                'long_name' => 'Texas',
                                'formatted_address' => 'Texas, USA',
                            ],
                            'exclusions' => [
                                [
                                    'place_id' => 'ChIJAYWNSLS4QIYROwVl894CDco',
                                    'long_name' => 'Houston',
                                    'formatted_address' => 'Houston, TX, USA',
                                ],
                            ],
                        ],
                    ],
                    'loan_size' => [
                        'max' => 15000000,
                        'min' => 5000000,
                    ],
                    'asset_types' => [1, 8],
                    'multifamily' => null,
                ],
            ],
        ]);

        $this->lender2 = Lender::factory()->create([
            'referrer_id' => null,
            'metas' => [
                'perfect_fit' => [
                    'areas' => [
                        [
                            'area' => [
                                'place_id' => 'ChIJOwg_06VPwokRYv534QaPC8g',
                                'long_name' => 'New York',
                                'formatted_address' => 'New York, NY, USA',
                            ],
                            'exclusions' => [
                                [
                                    'place_id' => 'ChIJsXxpOlWLwokRd1zxj6dDblU',
                                    'long_name' => 'The Bronx',
                                    'formatted_address' => 'The Bronx, NY, USA',
                                ],
                                [
                                    'place_id' => 'ChIJCSF8lBZEwokRhngABHRcdoI',
                                    'long_name' => 'Brooklyn',
                                    'formatted_address' => 'Brooklyn, NY, USA',
                                ],
                            ],
                        ],
                        [
                            'area' => [
                                'place_id' => 'ChIJvypWkWV2wYgR0E7HW9MTLvc',
                                'long_name' => 'Florida',
                                'formatted_address' => 'Florida, USA',
                            ],
                            'exclusions' => [],
                        ],
                        [
                            'area' => [
                                'place_id' => 'ChIJSTKCCzZwQIYRPN4IGI8c6xY',
                                'long_name' => 'Texas',
                                'formatted_address' => 'Texas, USA',
                            ],
                            'exclusions' => [
                                [
                                    'place_id' => 'ChIJAYWNSLS4QIYROwVl894CDco',
                                    'long_name' => 'Houston',
                                    'formatted_address' => 'Houston, TX, USA',
                                ],
                            ],
                        ],
                    ],
                    'loan_size' => [
                        'max' => 15000000,
                        'min' => 5000000,
                    ],
                    'asset_types' => [4, 2],
                    'multifamily' => null,
                ],
            ],
        ]);

        $this->lender3 = Lender::factory()->create([
            'referrer_id' => null,
            'metas' => [
                'perfect_fit' => [
                    'areas' => [
                        [
                            'area' => [
                                'place_id' => 'ChIJOwg_06VPwokRYv534QaPC8g',
                                'long_name' => 'New York',
                                'formatted_address' => 'New York, NY, USA',
                            ],
                            'exclusions' => [
                                [
                                    'place_id' => 'ChIJsXxpOlWLwokRd1zxj6dDblU',
                                    'long_name' => 'The Bronx',
                                    'formatted_address' => 'The Bronx, NY, USA',
                                ],
                                [
                                    'place_id' => 'ChIJCSF8lBZEwokRhngABHRcdoI',
                                    'long_name' => 'Brooklyn',
                                    'formatted_address' => 'Brooklyn, NY, USA',
                                ],
                            ],
                        ],
                        [
                            'area' => [
                                'place_id' => 'ChIJvypWkWV2wYgR0E7HW9MTLvc',
                                'long_name' => 'Florida',
                                'formatted_address' => 'Florida, USA',
                            ],
                            'exclusions' => [],
                        ],
                        [
                            'area' => [
                                'place_id' => 'ChIJSTKCCzZwQIYRPN4IGI8c6xY',
                                'long_name' => 'Texas',
                                'formatted_address' => 'Texas, USA',
                            ],
                            'exclusions' => [
                                [
                                    'place_id' => 'ChIJAYWNSLS4QIYROwVl894CDco',
                                    'long_name' => 'Houston',
                                    'formatted_address' => 'Houston, TX, USA',
                                ],
                            ],
                        ],
                    ],
                    'loan_size' => [
                        'max' => 15000000,
                        'min' => 5000000,
                    ],
                    'asset_types' => [8],
                    'multifamily' => [
                        'min_amount' => 2,
                        'max_amount' => 60,
                    ],
                ],
            ],
        ]);

        $broker = Broker::factory()->create();
        AssetTypes::factory()->create([
            'id' => 1,
            'title' => 'Retail',
        ]);
        AssetTypes::factory()->create([
            'id' => 2,
            'title' => 'Office',
        ]);
        AssetTypes::factory()->create([
            'id' => 3,
            'title' => 'Industrial',
        ]);
        AssetTypes::factory()->create([
            'id' => 4,
            'title' => 'Mixed use',
        ]);
        AssetTypes::factory()->create([
            'id' => 5,
            'title' => 'Construction',
        ]);
        AssetTypes::factory()->create([
            'id' => 6,
            'title' => 'Owner occupied',
        ]);
        AssetTypes::factory()->create([
            'id' => 8,
            'title' => 'Multifamily',
        ]);

        //Purchase - Multifamily
        $deal = Deal::factory()->create(['id' => 1,
            'user_id' => $broker->id,
            'data' => [
                'inducted' => [
                    'loan_type ' => 1,
                    'property_type' => [
                        'type' => 1,
                        'asset_types' => [8],
                        'mixed' => false,
                        'multifamilyAmount' => 22,
                    ],
                ],
            ],
        ]);
        $assetTypes = DealAssetType::factory()->create(
            [
                'deal_id' => 1,
                'asset_type_id' => 8,
            ]
        );

        //Purchase - Investment - Retail
        $deal1 = Deal::factory()->create(['id' => 2,
            'user_id' => $broker->id,
            'data' => [
                'inducted' => [
                    'loan_type ' => 1,
                    'property_type' => [
                        'type' => 1,
                        'asset_types' => [1],
                        'mixed' => false,
                        'multifamilyAmount' => 0,
                    ],
                ],
            ],
        ]);
        $assetTypes1 = DealAssetType::factory()->create(
            [
                'deal_id' => 2,
                'asset_type_id' => 1,
            ]
        );

        //Purchase - Mixed use - Office
        $deal2 = Deal::factory()->create(['id' => 3,
            'user_id' => $broker->id,
            'data' => [
                'inducted' => [
                    'loan_type ' => 1,
                    'property_type' => [
                        'type' => 1,
                        'asset_types' => [4],
                        'mixed' => true,
                        'multifamilyAmount' => 0,
                    ],
                ],
            ],
        ]);
        $assetTypes2 = DealAssetType::factory()->create(
            [
                'deal_id' => 3,
                'asset_type_id' => 4,
            ]
        );

        //Purchase - Owner occupied
        $deal3 = Deal::factory()->create(['id' => 4,
            'user_id' => $broker->id,
            'data' => [
                'inducted' => [
                    'loan_type ' => 1,
                    'property_type' => [
                        'type' => 2,
                        'asset_types' => [6],
                        'mixed' => false,
                        'multifamilyAmount' => 0,
                    ],
                ],
            ],
        ]);
        $assetTypes3 = DealAssetType::factory()->create(
            [
                'deal_id' => 4,
                'asset_type_id' => 6,
            ]
        );

        //Purchase - Construction
        $deal4 = Deal::factory()->create(['id' => 5,
            'user_id' => $broker->id,
            'data' => [
                'inducted' => [
                    'loan_type ' => 1,
                    'property_type' => [
                        'type' => 3,
                        'asset_types' => [5, 3],
                        'mixed' => false,
                        'multifamilyAmount' => 0,
                    ],
                ],
            ],
        ]);
        $assetTypes4 = DealAssetType::factory()->create(
            [
                'deal_id' => 5,
                'asset_type_id' => 5,
            ]
        );
        $assetTypes5 = DealAssetType::factory()->create(
            [
                'deal_id' => 5,
                'asset_type_id' => 3,
            ]
        );

        //Purchase - Mixed use
        $deal5 = Deal::factory()->create(['id' => 6,
            'user_id' => $broker->id,
            'data' => [
                'inducted' => [
                    'loan_type ' => 1,
                    'property_type' => [
                        'type' => 1,
                        'asset_types' => [4],
                        'mixed' => true,
                        'multifamilyAmount' => 0,
                    ],
                ],
            ],
        ]);
        $assetTypes6 = DealAssetType::factory()->create(
            [
                'deal_id' => 6,
                'asset_type_id' => 4,
            ]
        );
    }

    /**
     * Test only Owner occupied and Construction
     */
    public function testAssetTypeConstructionAndOwner()
    {
        $dealPreferences = $this->lender->getPerfectFit();
        $args['assetType'] = $dealPreferences->getAssetTypes();
        $multyfamilyValues = $dealPreferences->getMultifamily();
        $args['min_amount'] = $multyfamilyValues['min_amount'] ?? 0;
        $args['max_amount'] = $multyfamilyValues['max_amount'] ?? 0;
        $args['query'] = $this->queryService;

        $deals = $this->serviceFilterByAsssetType->run($args);

        $this->assertTrue($deals->contains(4));
        $this->assertTrue($deals->contains(5));
    }

    /**
     * Test Retail, Office, Industrial, Land, not Mixed use
     */
    public function testAssetType()
    {
        $dealPreferences = $this->lender1->getPerfectFit();
        $args['assetType'] = $dealPreferences->getAssetTypes();
        $multyfamilyValues = $dealPreferences->getMultifamily();
        $args['min_amount'] = $multyfamilyValues['min_amount'] ?? 0;
        $args['max_amount'] = $multyfamilyValues['max_amount'] ?? 0;
        $args['query'] = $this->queryService;

        $deals = $this->serviceFilterByAsssetType->run($args);

        $this->assertTrue($deals->contains(1));
        $this->assertTrue($deals->contains(2));
    }

    /**
     * Test Mixed use
     */
    public function testAssetTypeMixedUse()
    {
        $dealPreferences = $this->lender2->getPerfectFit();
        $args['assetType'] = $dealPreferences->getAssetTypes();
        $multyfamilyValues = $dealPreferences->getMultifamily();
        $args['min_amount'] = $multyfamilyValues['min_amount'] ?? 0;
        $args['max_amount'] = $multyfamilyValues['max_amount'] ?? 0;
        $args['query'] = $this->queryService;

        $deals = $this->serviceFilterByAsssetType->run($args);

        $this->assertTrue($deals->contains(3));
        $this->assertTrue($deals->contains(6));
    }
}
