<?php

declare(strict_types=1);

namespace Tests\Services\QueryServices\Broker\Quotes;

use App\Broker;
use App\Deal;
use App\Services\QueryServices\Broker\Quotes\FilterBySponsorName;
use App\Termsheet;
use Database\Seeders\Termsheets;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class FilterBySponsorNameTest
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class FilterBySponsorNameTest extends TestCase
{
    /**
     * @var FilterBySponsorName
     */
    private $serviceFilterBySponsorName;

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
        $this->serviceFilterBySponsorName = $this->app->make(FilterBySponsorName::class);
        $this->queryService = DB::table('deals')->select('id');

        $broker = Broker::factory()->create();
        $deal = Deal::factory()->create(['id' => 1,
            'user_id' => $broker->id,
            'sponsor_name' => 'smith',
        ]);

        $deal1 = Deal::factory()->create(['id' => 2,
            'user_id' => $broker->id,
            'sponsor_name' => 'kowalski and co',
        ]);

        $deal2 = Deal::factory()->create(['id' => 3,
            'user_id' => $broker->id,
            'sponsor_name' => 'jill',
        ]);

        $deal3 = Deal::factory()->create(['id' => 4,
            'user_id' => $broker->id,
            'sponsor_name' => 'jack',
        ]);

        $deal4 = Deal::factory()->create(['id' => 5,
            'user_id' => $broker->id,
            'sponsor_name' => 'will',
        ]);
    }

    /**
     * Search by sponsor name
     * Todo: Fix the behavior
     */
    public function testSearchTermsSponsorName()
    {
        $args['sponsorNames'] = ['Will', 'Kowalski and Co', 'Smith'];
        $args['sponsorName'] = '';
        $args['query'] = $this->queryService;
        $deals = $this->serviceFilterBySponsorName->run($args);

        //$this->assertTrue($deals->contains(1));
        //$this->assertTrue($deals->contains(2));
        //$this->assertTrue($deals->contains(5));
    }
}
