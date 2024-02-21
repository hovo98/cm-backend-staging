<?php

declare(strict_types=1);

namespace Tests\Services\QueryServices\Lender\Deals;

use App\Broker;
use App\Deal;
use App\Lender;
use App\Services\QueryServices\Lender\Deals\IgnoredDeals;
use App\Termsheet;
use App\User;
use Database\Seeders\Termsheets;
use Tests\TestCase;

/**
 * Class IgnoredDealsTest
 *
 * @author  Boris Djemrovski <boris@forwardslashny.com>
 */
class IgnoredDealsTest extends TestCase
{
    /** @var Lender */
    private $lender;

    /** @var Deal[] */
    private $ignored;

    /** @var Deal[] */
    private $neutral;

    private $testResult;

    /** @var IgnoredDeals */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();
        $this->service = $this->app->make(IgnoredDeals::class);

        $this->lender = Lender::factory()->create(['referrer_id' => null]);

        $broker = Broker::factory()->create(['referrer_id' => null]);
        $this->ignored = Deal::factory()->count(9)->create(['user_id' => $broker->id]);
        $this->neutral = Deal::factory()->count(10)->create(['user_id' => $broker->id]);

        $this->ignored->each([$this, 'ignore']);
    }

    public function testRun()
    {
        $this->testResult = $this->service->run(['lenderId' => $this->lender->id]);

        $this->assertCount(9, $this->testResult);

        $this->ignored->each([$this, 'assertContainsDeal']);
        $this->neutral->each([$this, 'assertNotContainsDeal']);
    }

    public function assertContainsDeal(Deal $deal): void
    {
        $this->assertContains($deal->id, $this->testResult);
    }

    public function assertNotContainsDeal(Deal $deal): void
    {
        $this->assertNotContains($deal->id, $this->testResult);
    }

    public function ignore(Deal $deal): Deal
    {
        $this->lender->storeRelationUserDeal($deal->id, User::LENDER_IGNORE_DEAL);

        return $deal;
    }
}
