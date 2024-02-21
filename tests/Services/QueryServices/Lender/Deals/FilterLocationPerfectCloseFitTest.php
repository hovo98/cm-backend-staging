<?php

declare(strict_types=1);

namespace Tests\Services\QueryServices\Lender\Deals;

use App\Broker;
use App\Deal;
use App\Lender;
use App\Services\QueryServices\Lender\Deals\FilterByLocation;
use App\Services\QueryServices\Lender\Deals\FilterLocationPerfectCloseFit;
use App\Services\QueryServices\Lender\Deals\GetPerfectCloseFitExcludedAreas;
use App\Termsheet;
use Database\Seeders\Termsheets;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class FilterByLocationTest
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class FilterLocationPerfectCloseFitTest extends TestCase
{
    /**
     * @var GetPerfectCloseFitExcludedAreas
     */
    private $serviceExcluded;

    /**
     * @var FilterLocationPerfectCloseFit
     */
    private $serviceLocationPerfectCloseFit;

    /**
     * @var FilterByLocation
     */
    private $servicefilterByLocation;

    /**
     * @var Builder
     */
    private $queryService;

    /**
     * @var Lender
     */
    private $lender;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();
        $this->serviceExcluded = $this->app->make(GetPerfectCloseFitExcludedAreas::class);
        $this->serviceLocationPerfectCloseFit = $this->app->make(FilterLocationPerfectCloseFit::class);
        $this->servicefilterByLocation = $this->app->make(FilterByLocation::class);
        $this->queryService = DB::table('deals')->select('id');

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
                                'state' => 'New York',
                                'county' => '',
                                'country' => 'United States',
                                'zip_code' => '',
                                'city' => 'New York',
                                'sublocality' => '',
                            ],
                            'exclusions' => [
                                [
                                    'place_id' => 'ChIJsXxpOlWLwokRd1zxj6dDblU',
                                    'long_name' => 'Bronx',
                                    'formatted_address' => 'The Bronx, NY, USA',
                                    'state' => 'New York',
                                    'county' => 'Bronx County',
                                    'country' => 'United States',
                                    'zip_code' => '',
                                    'sublocality' => 'Bronx',
                                    'city' => '',
                                ],
                                [
                                    'place_id' => 'ChIJCSF8lBZEwokRhngABHRcdoI',
                                    'long_name' => 'Brooklyn',
                                    'formatted_address' => 'Brooklyn, NY, USA',
                                    'state' => 'New York',
                                    'county' => 'Kings County',
                                    'country' => 'United States',
                                    'zip_code' => '',
                                    'sublocality' => 'Brooklyn',
                                    'city' => 'New York',
                                ],
                            ],
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
                            'exclusions' => [
                                [
                                    'place_id' => 'ChIJAYWNSLS4QIYROwVl894CDco',
                                    'long_name' => 'Houston',
                                    'formatted_address' => 'Houston, TX, USA',
                                    'state' => 'Texas',
                                    'county' => 'Harris County',
                                    'country' => 'United States',
                                    'zip_code' => '',
                                    'sublocality' => '',
                                    'city' => 'Houston',
                                ],
                            ],
                        ],
                    ],
                    'loan_size' => [
                        'max' => 15000000,
                        'min' => 5000000,
                    ],
                    'asset_types' => [5],
                    'multifamily' => null,
                ],
            ],
        ]);

        $broker = Broker::factory()->create(['referrer_id' => null]);

        $deal = Deal::factory()->create(['id' => 1,
            'user_id' => $broker->id,
            'data' => [
                'location' => [
                    'city' => 'New York',
                    'state' => 'New York',
                    'place_id' => 'ChIJ7y286Q1bwokREMOhksLTL8o',
                    'zip_code' => '11225',
                    'sublocality' => 'Brooklyn',
                    'street_address' => '450 Flatbush Ave',
                    'street_address_2' => '',
                    'county' => 'Kings County',
                    'street' => 'Flatbush Avenue',
                    'country' => 'United States',
                ],
            ],
        ]);

        $deal1 = Deal::factory()->create(['id' => 2,
            'user_id' => $broker->id,
            'data' => [
                'location' => [
                    'city' => 'Freeport',
                    'state' => 'New York',
                    'place_id' => 'ChIJ7y286Q1bwokREMOhksLTL8o',
                    'zip_code' => '11520',
                    'sublocality' => '',
                    'street_address' => 'East Sunrise Highway',
                    'street_address_2' => '',
                    'county' => 'Nassau County',
                    'street' => 'East Sunrise Highway',
                    'country' => 'United States',
                ],
            ],
        ]);

        $deal2 = Deal::factory()->create(['id' => 3,
            'user_id' => $broker->id,
            'data' => [
                'location' => [
                    'city' => 'Tampa',
                    'state' => 'Florida',
                    'place_id' => 'ChIJC5ISmsHFwogROFfwVpGkhMw',
                    'zip_code' => '33610',
                    'sublocality' => '',
                    'street_address' => '4202 E Lake Ave',
                    'street_address_2' => '',
                    'county' => 'Hillsborough County',
                    'street' => 'East Lake Avenue',
                    'country' => 'United States',
                ],
            ],
        ]);

        $deal3 = Deal::factory()->create(['id' => 4,
            'user_id' => $broker->id,
            'data' => [
                'location' => [
                    'city' => 'Arlington',
                    'state' => 'Texas',
                    'place_id' => 'ChIJ7y286Q1bwokREMOhksLTL8o',
                    'zip_code' => '76011',
                    'sublocality' => '',
                    'street_address' => '1 AT&T Way',
                    'street_address_2' => '',
                    'county' => 'Tarrant County',
                    'street' => 'AT&T Way',
                    'country' => 'United States',
                ],
            ],
        ]);

        $deal4 = Deal::factory()->create(['id' => 5,
            'user_id' => $broker->id,
            'data' => [
                'location' => [
                    'city' => 'Lathrop',
                    'state' => 'California',
                    'place_id' => 'EhlSb3RoIFJkLCBMYXRocm9wLCBDQSwgVVNBIi4qLAoUChIJ3Q3mSNEUkIAR3kHHmNcEz8ISFAoSCUM8EzdOFZCAEQ4sggwdHAYL',
                    'zip_code' => '11225',
                    'sublocality' => '',
                    'street_address' => 'Roth Road',
                    'street_address_2' => '',
                    'county' => 'San Joaquin County',
                    'street' => 'Roth Road',
                    'country' => 'United States',
                ],
            ],
        ]);
    }

    /**
     * Get only working areas
     */
    public function testWorkingLocation()
    {
        $deal_preferences = $this->lender->getPerfectFit();
        $workingAreas = $deal_preferences->getWorkingAreas();

        $this->assertIsArray($workingAreas);
        $this->assertCount(3, $workingAreas);
    }

    /**
     * Get ids of Deals in Working areas
     */
    public function testWorkingAreasDeals()
    {
        $deal_preferences = $this->lender->getPerfectFit();
        $args['locations'] = $deal_preferences->getWorkingAreas();
        $args['query'] = $this->queryService;
        $deals = $this->servicefilterByLocation->run($args);

        $this->assertTrue($deals->contains(3));
        $this->assertTrue($deals->contains(4));
    }

    /**
     * Get ids of Deals in Working areas and exclude ids from excluded areas
     */
    public function testLocationPerfectCloseFit()
    {
        $deal_preferences = $this->lender->getPerfectFit();
        $args['workingAreas'] = $deal_preferences->getWorkingAreas();
        $args['excludedArea'] = $deal_preferences->getExcludedAreas();
        $args['query'] = $this->queryService;

        $deals = $this->serviceLocationPerfectCloseFit->run($args);

        $this->assertTrue($deals->contains(3));
        $this->assertTrue($deals->contains(4));
    }
}
