<?php

declare(strict_types=1);

namespace Tests\Services\QueryServices\Broker\Quotes;

use App\Broker;
use App\Deal;
use App\Services\QueryServices\Broker\Quotes\FilterBySearchLocation;
use App\Termsheet;
use Database\Seeders\Termsheets;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Class FilterBySearchLocationTest
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class FilterBySearchLocationTest extends TestCase
{
    /**
     * @var FilterBySearchLocation
     */
    private $serviceFilterBySearchLocation;

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
        $this->serviceFilterBySearchLocation = $this->app->make(FilterBySearchLocation::class);

        $this->queryService = DB::table('deals')->select('id');
        $broker = Broker::factory()->create(['referrer_id' => null]);

        $deal = Deal::factory()->create(['id' => 1,
            'user_id' => $broker->id,
            'location' => 'new york new york brooklyn 450 flatbush ave 11225',
        ]);

        $deal1 = Deal::factory()->create(['id' => 2,
            'user_id' => $broker->id,
            'location' => 'new york freeport freeport east sunrise highway 13225',
        ]);

        $deal2 = Deal::factory()->create(['id' => 3,
            'user_id' => $broker->id,
            'location' => 'florida tampa tampa 4202 e lake ave 33610',
        ]);

        $deal3 = Deal::factory()->create(['id' => 4,
            'user_id' => $broker->id,
            'location' => 'texas arlington arlington 1 at&t way 76011',
        ]);

        $deal4 = Deal::factory()->create(['id' => 5,
            'user_id' => $broker->id,
            'location' => 'california lathrop lathrop roth road 11225',
        ]);
    }

    /**
     * Search only by location to return multiple deals
     */
    public function testSearchLocation()
    {
        $args['searchLocation'] = 'New York';
        $args['query'] = $this->queryService;
        $deals = $this->serviceFilterBySearchLocation->run($args);

        $this->assertTrue($deals->contains(1));
        $this->assertTrue($deals->contains(2));
    }
}
