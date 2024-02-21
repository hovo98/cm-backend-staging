<?php

declare(strict_types=1);

namespace Tests\Services\QueryServices\Lender\Deals;

use App\Broker;
use App\Deal;
use App\Lender;
use App\Services\QueryServices\Lender\Deals\IgnoredDealsMultipleLenders;
use App\Termsheet;
use App\User;
use Database\Seeders\Termsheets;
use Tests\TestCase;

/**
 * Class IgnoredDealsMultipleLendersTest
 *
 * @author  Boris Djemrovski <boris@forwardslashny.com>
 */
class IgnoredDealsMultipleLendersTest extends TestCase
{
    /** @var Lender */
    private $lender1;

    /** @var Lender */
    private $lender2;

    /** @var Deal[] */
    private $ignored;

    /** @var Deal[] */
    private $notIgnored;

    /** @var IgnoredDealsMultipleLenders */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();
        $this->service = $this->app->make(IgnoredDealsMultipleLenders::class);

        $this->lender1 = Lender::factory()->create(['referrer_id' => null]);
        $this->lender2 = Lender::factory()->create(['referrer_id' => null]);

        $broker = Broker::factory()->create(['referrer_id' => null]);

        $deals1 = collect(Deal::factory()->count(16)->create(['user_id' => $broker->id]));
        $deals2 = collect(Deal::factory()->count(10)->create(['user_id' => $broker->id]));

        $ignored1 = $deals1->random(9);
        $ignored2 = $deals2->random(6);

        $ignored1->each(function ($deal) {
            $this->lender1->storeRelationUserDeal($deal->id, User::LENDER_IGNORE_DEAL);
        });

        $ignored2->each(function ($deal) {
            $this->lender2->storeRelationUserDeal($deal->id, User::LENDER_IGNORE_DEAL);
        });

        $this->ignored = $ignored1->merge($ignored2);
        $this->notIgnored = collect($deals1)->merge($deals2)->diff($this->ignored);
    }

    public function testRun()
    {
        $result = $this->service->run(['in' => [$this->lender1->id, $this->lender2->id]]);

        $this->ignored->each(function ($deal) use ($result) {
            $this->assertContains($deal->id, $result);
        });

        $this->notIgnored->each(function ($deal) use ($result) {
            $this->assertNotContains($deal->id, $result);
        });
    }
}
