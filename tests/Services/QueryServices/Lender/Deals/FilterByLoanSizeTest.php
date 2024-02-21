<?php

declare(strict_types=1);

namespace Tests\Services\QueryServices\Lender\Deals;

use App\Broker;
use App\Deal;
use App\Services\QueryServices\Lender\Deals\FilterByLoanSize;
use App\Termsheet;
use Database\Seeders\Termsheets;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class FilterByLoanSizeTest
 *
 * @author  Boris Djemrovski <boris@forwardslashny.com>
 */
class FilterByLoanSizeTest extends TestCase
{
    /**
     * @var FilterByLoanSize
     */
    private $service;

    /**
     * @var Builder
     */
    private $queryService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();
        $this->service = $this->app->make(FilterByLoanSize::class);
        $this->queryService = DB::table('deals')->select('id');

        $broker = Broker::factory()->create();

        // Empty deals
        Deal::factory()->count(10)->create(['user_id' => $broker->id]);

        // Purchase Loan deals
        Deal::factory()->count(11)->create([
            'user_id' => $broker->id,
            'dollar_amount' => 7,
        ]);

        // Refinance Loan deals
        Deal::factory()->count(12)->create([
            'user_id' => $broker->id,
            'dollar_amount' => 70,
        ]);

        // Construction Loan deals
        Deal::factory()->count(13)->create([
            'user_id' => $broker->id,
            'dollar_amount' => 700,
        ]);
    }

    public function testPurchaseLoan()
    {
        $purchaseLoans = $this->service->run(['min' => 1, 'max' => 69, 'query' => $this->queryService]);
        self::assertCount(11, $purchaseLoans);
    }

    public function testRefinanceLoan()
    {
        $refinanceLoans = $this->service->run(['min' => 69, 'max' => 70, 'query' => $this->queryService]);
        self::assertCount(12, $refinanceLoans);
    }

    public function testConstructionLoan()
    {
        $constructionLoans = $this->service->run(['min' => 700, 'max' => 777, 'query' => $this->queryService]);
        self::assertCount(13, $constructionLoans);
    }
}
