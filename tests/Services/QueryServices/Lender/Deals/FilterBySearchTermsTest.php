<?php

declare(strict_types=1);

namespace Tests\Services\QueryServices\Lender\Deals;

use App\Broker;
use App\Deal;
use App\Services\QueryServices\Lender\Deals\FilterBySearchTerms;
use App\Termsheet;
use Database\Seeders\Termsheets;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class FilterBySearchTermsTest
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class FilterBySearchTermsTest extends TestCase
{
    /**
     * @var FilterBySearchTerms
     */
    private $serviceFilterBySearchTerms;

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
        $this->serviceFilterBySearchTerms = $this->app->make(FilterBySearchTerms::class);
        $this->queryService = DB::table('deals')->select('id');

        $broker = Broker::factory()->create();

        $deal = Deal::factory()->create(['id' => 1,
            'user_id' => $broker->id,
            'location' => 'new york new york brooklyn 450 flatbush ave 11225',
            'sponsor_name' => 'anna',
        ]);

        $deal1 = Deal::factory()->create(['id' => 2,
            'user_id' => $broker->id,
            'location' => 'new york freeport freeport east sunrise highway 11225',
            'sponsor_name' => 'company',
        ]);

        $deal2 = Deal::factory()->create(['id' => 3,
            'user_id' => $broker->id,
            'location' => 'florida tampa tampa 4202 e lake ave 33610',
            'sponsor_name' => 'jill',
        ]);

        $deal3 = Deal::factory()->create(['id' => 4,
            'user_id' => $broker->id,
            'location' => 'texas arlington arlington 1 at&t way 76011',
            'sponsor_name' => 'jill',
        ]);

        $deal4 = Deal::factory()->create(['id' => 5,
            'user_id' => $broker->id,
            'location' => 'california lathrop lathrop roth road 11225',
            'sponsor_name' => 'will',
        ]);
    }

    /**
     * Search only by location
     */
    public function testSearchTermsLocation()
    {
        $args['searchTerms'] = 'new york';
        $args['query'] = $this->queryService;
        $deals = $this->serviceFilterBySearchTerms->run($args);

        $this->assertTrue($deals->contains(1));
        $this->assertTrue($deals->contains(2));
    }

    /**
     * Search by sponsor name
     */
    public function testSearchTermsSponsorName()
    {
        $args['searchTerms'] = 'jill';
        $args['query'] = $this->queryService;
        $deals = $this->serviceFilterBySearchTerms->run($args);

        $this->assertTrue($deals->contains(3));
        $this->assertTrue($deals->contains(4));
    }
}
