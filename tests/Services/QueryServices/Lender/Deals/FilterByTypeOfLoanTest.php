<?php

declare(strict_types=1);

namespace Tests\Services\QueryServices\Lender\Deals;

use App\Broker;
use App\Deal;
use App\DealTypeOfLoan;
use App\Services\QueryServices\Lender\Deals\FilterByTypeOfLoan;
use App\Termsheet;
use Database\Seeders\Termsheets;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class FilterByTypeOfLoanTest
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class FilterByTypeOfLoanTest extends TestCase
{
    /**
     * @var FilterByTypeOfLoan
     */
    private $serviceFilterByTypeOfLoan;

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
        $this->serviceFilterByTypeOfLoan = $this->app->make(FilterByTypeOfLoan::class);
        $this->queryService = DB::table('deals')->whereNull('deleted_at')
            ->where('finished', '=', true)->select('deals.id');

        $broker = Broker::factory()->create();
        DealTypeOfLoan::factory()->create([
            'deal_id' => 10,
            'type_of_loan' => 1,
        ]);
        DealTypeOfLoan::factory()->create([
            'deal_id' => 11,
            'type_of_loan' => 1,
        ]);
        DealTypeOfLoan::factory()->create([
            'deal_id' => 20,
            'type_of_loan' => 2,
        ]);
        DealTypeOfLoan::factory()->create([
            'deal_id' => 22,
            'type_of_loan' => 2,
        ]);
        DealTypeOfLoan::factory()->create([
            'deal_id' => 30,
            'type_of_loan' => 3,
        ]);
        DealTypeOfLoan::factory()->create([
            'deal_id' => 43,
            'type_of_loan' => 4,
        ]);
        DealTypeOfLoan::factory()->create([
            'deal_id' => 43,
            'type_of_loan' => 3,
        ]);
        DealTypeOfLoan::factory()->create([
            'deal_id' => 40,
            'type_of_loan' => 4,
        ]);
        $deal1 = Deal::factory()->create([
            'id' => 10,
            'user_id' => $broker->id,
        ]);
        $deal2 = Deal::factory()->create([
            'id' => 20,
            'user_id' => $broker->id,
        ]);
        $deal3 = Deal::factory()->create([
            'id' => 30,
            'user_id' => $broker->id,
        ]);
        $deal4 = Deal::factory()->create([
            'id' => 40,
            'user_id' => $broker->id,
        ]);
        $deal43 = Deal::factory()->create([
            'id' => 43,
            'user_id' => $broker->id,
        ]);
        $deal11 = Deal::factory()->create([
            'id' => 11,
            'user_id' => $broker->id,
        ]);
        $deal22 = Deal::factory()->create([
            'id' => 22,
            'user_id' => $broker->id,
        ]);
    }

    /**
     * Test one type of loan
     */
    public function testOneTypeOfLoan()
    {
        $args['query'] = $this->queryService;
        $args['type_of_loans'] = [4];
        $deals = $this->serviceFilterByTypeOfLoan->run($args);

        $this->assertTrue($deals->contains(43));
        $this->assertTrue($deals->contains(40));
    }

    /**
     * Test multiple types of loan
     */
    public function testTypeOfLoans()
    {
        $args['query'] = $this->queryService;
        $args['type_of_loans'] = [1, 2];
        $deals = $this->serviceFilterByTypeOfLoan->run($args);

        $this->assertTrue($deals->contains(10));
        $this->assertTrue($deals->contains(11));
        $this->assertTrue($deals->contains(20));
        $this->assertTrue($deals->contains(22));
    }
}
