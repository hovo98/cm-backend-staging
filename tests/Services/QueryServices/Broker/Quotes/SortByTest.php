<?php

declare(strict_types=1);

namespace Tests\Services\QueryServices\Broker\Quotes;

use App\AssetTypes;
use App\Broker;
use App\Deal;
use App\DealAssetType;
use App\Lender;
use App\Quote;
use App\Services\QueryServices\Broker\Quotes\SortBy;
use App\Termsheet;
use Database\Seeders\Termsheets;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class SortByTest
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class SortByTest extends TestCase
{
    /**
     * used Quote fields for sorting
     *
     * dollar amount
     * $quote['constructionLoans']['requestedLoan']['dollarAmount'];
     * $quote['purchaseAndRefinanceLoans']['offer']['amount']
     *
     * interest rate
     * $rateAmount = $quote['constructionLoans']['interestRate']['fixedRateAmount'];
     * $quote['purchaseAndRefinanceLoans']['interestRate']['fixedRateAmount'];
     *
     * rate term
     * $constructionTerm = $quote['constructionLoans']['constructionTerm'];
     * $quote['purchaseAndRefinanceLoans']['amountOfYears']
     *
     * origination fee
     * $costAmount = $quote['purchaseAndRefinanceLoans']['collectingOrigination']['costAmount'];
     * $constructionCostPercent = $quote['constructionLoans']['collectingFee']['feeAmount'];
     */

    /**
     * @var Builder
     */
    private $queryService;

    /**
     * @var SortBy
     */
    private $sortByService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();

        AssetTypes::factory()->create([
            'id' => 1,
            'title' => 'Retail',
        ]);
        AssetTypes::factory()->create([
            'id' => 3,
            'title' => 'Industrial',
        ]);
        AssetTypes::factory()->create([
            'id' => 5,
            'title' => 'Construction',
        ]);

        $this->sortByService = $this->app->make(SortBy::class);
        $this->queryService = DB::table('quotes')->select('id');

        $lender = Lender::factory()->create(['referrer_id' => null]);
        $broker = Broker::factory()->create(['referrer_id' => null]);
        $deal = Deal::factory()->create(['id' => 3,
            'user_id' => $broker->id,
            'data' => [
                'location' => [
                    'city' => 'New York',
                    'state' => 'New York',
                    'place_id' => 'ChIJ7y286Q1bwokREMOhksLTL8o',
                    'zip_code' => '11225',
                    'sublocality' => 'Queens',
                    'street_address' => '450 Flatbush Ave',
                    'street_address_2' => '',
                ],
            ],
        ]);
        $assetTypes = DealAssetType::factory()->create(
            [
                'deal_id' => 3,
                'asset_type_id' => 5,
            ]
        );
        $assetTypes1 = DealAssetType::factory()->create(
            [
                'deal_id' => 3,
                'asset_type_id' => 3,
            ]
        );

        $deal1 = Deal::factory()->create(['id' => 9,
            'user_id' => $broker->id,
            'data' => [
                'location' => [
                    'city' => 'Austin',
                    'state' => 'Texas',
                    'place_id' => 'ChIJ7y286Q1bwokREMOhksLTL8o',
                    'zip_code' => '11225',
                    'sublocality' => 'Austin',
                    'street_address' => '450 Flatbush Ave',
                    'street_address_2' => '',
                ],
            ],
        ]);
        $assetTypes2 = DealAssetType::factory()->create(
            [
                'deal_id' => 9,
                'asset_type_id' => 5,
            ]
        );
        $assetTypes3 = DealAssetType::factory()->create(
            [
                'deal_id' => 9,
                'asset_type_id' => 3,
            ]
        );

        $deal2 = Deal::factory()->create(['id' => 8,
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
                ],
            ],
        ]);

        $assetTypes4 = DealAssetType::factory()->create(
            [
                'deal_id' => 8,
                'asset_type_id' => 1,
            ]
        );

        $quote = Quote::factory()->create(['id' => 1,
            'user_id' => $lender->id,
            'deal_id' => $deal2->id,
            'dollar_amount' => 22,
            'interest_rate' => 22.22,
            'interest_rate_spread' => null,
            'rate_term' => 2.8,
            'origination_fee' => 10,
            'origination_fee_spread' => null,
        ]);

        $quote1 = Quote::factory()->create(['id' => 2,
            'user_id' => $lender->id,
            'deal_id' => $deal1->id,
            'dollar_amount' => 15,
            'interest_rate' => null,
            'interest_rate_spread' => 22.22,
            'rate_term' => 4,
            'origination_fee' => null,
            'origination_fee_spread' => 4.9,
        ]);

        $quote2 = Quote::factory()->create(['id' => 3,
            'user_id' => $lender->id,
            'deal_id' => $deal->id,
            'dollar_amount' => 10,
            'interest_rate' => 5,
            'interest_rate_spread' => null,
            'rate_term' => 9,
            'origination_fee' => 2,
            'origination_fee_spread' => null,
        ]);

        $quote3 = Quote::factory()->create(['id' => 4,
            'user_id' => $lender->id,
            'deal_id' => $deal->id,
            'dollar_amount' => 50,
            'interest_rate' => null,
            'interest_rate_spread' => 3.87,
            'rate_term' => 5.3,
            'origination_fee' => null,
            'origination_fee_spread' => 3.4,
        ]);

        $quote4 = Quote::factory()->create(['id' => 5,
            'user_id' => $lender->id,
            'deal_id' => $deal2->id,
            'dollar_amount' => 21,
            'interest_rate' => 2.4,
            'interest_rate_spread' => null,
            'rate_term' => 0.2,
            'origination_fee' => 14,
            'origination_fee_spread' => null,
        ]);

        $quote5 = Quote::factory()->create(['id' => 6,
            'user_id' => $lender->id,
            'deal_id' => $deal2->id,
            'dollar_amount' => 22,
            'interest_rate' => 2.21,
            'interest_rate_spread' => null,
            'rate_term' => 35,
            'origination_fee' => null,
            'origination_fee_spread' => 8.2,
        ]);

        $quote6 = Quote::factory()->create(['id' => 7,
            'user_id' => $lender->id,
            'deal_id' => $deal2->id,
            'dollar_amount' => 21,
            'interest_rate' => null,
            'interest_rate_spread' => 0.5,
            'rate_term' => 2,
            'origination_fee' => 90,
            'origination_fee_spread' => null,
        ]);

        $quote7 = Quote::factory()->create(['id' => 8,
            'user_id' => $lender->id,
            'deal_id' => $deal2->id,
            'dollar_amount' => 50,
            'interest_rate' => 7,
            'interest_rate_spread' => null,
            'rate_term' => 2.8,
            'origination_fee' => null,
            'origination_fee_spread' => 0.25,
        ]);

        $quote8 = Quote::factory()->create(['id' => 9,
            'user_id' => $lender->id,
            'deal_id' => $deal2->id,
            'dollar_amount' => 11,
            'interest_rate' => null,
            'interest_rate_spread' => 4.2,
            'rate_term' => 1.4,
            'origination_fee' => 55,
            'origination_fee_spread' => null,
        ]);
    }

    /**
     * Sort by location - Deal field
     */
    public function testSortByLocation()
    {
        $args['sortBy'] = [
            'sort' => 'location',
            'by' => 'DESC',
        ];
        $args['query'] = $this->queryService;
        $sortQuotes = $this->sortByService->run($args);
        $isSort = false;

        foreach ($sortQuotes as $key => $sortQuote) {
            if ($key === 0 && $sortQuote === 2) {
                $isSort = true;
            }
            if ($key === 4 && $sortQuote === 5) {
                $isSort = true;
            }
        }
        $this->assertTrue($isSort);
    }

    /**
     * Sort by property type - Deal field
     */
    public function testSortByPropertyType()
    {
        $args['sortBy'] = [
            'sort' => 'property_type',
            'by' => 'DESC',
        ];
        $args['query'] = $this->queryService;
        $sortQuotes = $this->sortByService->run($args);
        $isSort = false;

        foreach ($sortQuotes as $key => $sortQuote) {
            if ($key === 0 && $sortQuote === 2) {
                $isSort = true;
            }
            if ($key === 4 && $sortQuote === 5) {
                $isSort = true;
            }
        }
        $this->assertTrue($isSort);
    }

    /**
     * Sort by dollar amount - Quote field
     */
    public function testSortByDollarAmount()
    {
        $args['sortBy'] = [
            'sort' => 'dollar_amount',
            'by' => 'DESC',
        ];
        $args['query'] = $this->queryService;
        $sortQuotes = $this->sortByService->run($args);
        $isSort = false;

        foreach ($sortQuotes as $key => $sortQuote) {
            if ($key === 0 && $sortQuote === 8) {
                $isSort = true;
            }
            if ($key === 8 && $sortQuote === 3) {
                $isSort = true;
            }
        }
        $this->assertTrue($isSort);
    }

    /**
     * Sort by interest rate - Quote field
     */
    public function testSortByInterestRate()
    {
        $args['sortBy'] = [
            'sort' => 'interest_rate',
            'by' => 'DESC',
        ];
        $args['query'] = $this->queryService;
        $sortQuotes = $this->sortByService->run($args);
        $isSort = false;

        foreach ($sortQuotes as $key => $sortQuote) {
            if ($key === 0 && $sortQuote === 1) {
                $isSort = true;
            }
            if ($key === 8 && $sortQuote === 7) {
                $isSort = true;
            }
        }
        $this->assertTrue($isSort);
    }

    /**
     * Sort by rate term - Quote field
     */
    public function testSortByRateTerm()
    {
        $args['sortBy'] = [
            'sort' => 'rate_term',
            'by' => 'DESC',
        ];
        $args['query'] = $this->queryService;
        $sortQuotes = $this->sortByService->run($args);
        $isSort = false;

        foreach ($sortQuotes as $key => $sortQuote) {
            if ($key === 0 && $sortQuote === 6) {
                $isSort = true;
            }
            if ($key === 8 && $sortQuote === 5) {
                $isSort = true;
            }
        }
        $this->assertTrue($isSort);
    }

    /**
     * Sort by origination fee - Quote field
     */
    public function testSortByOriginationFee()
    {
        $args['sortBy'] = [
            'sort' => 'origination_fee',
            'by' => 'DESC',
        ];
        $args['query'] = $this->queryService;
        $sortQuotes = $this->sortByService->run($args);
        $isSort = false;

        foreach ($sortQuotes as $key => $sortQuote) {
            if ($key === 0 && $sortQuote === 6) {
                $isSort = true;
            }
            if ($key === 8 && $sortQuote === 3) {
                $isSort = true;
            }
        }
        $this->assertTrue($isSort);
    }
}
