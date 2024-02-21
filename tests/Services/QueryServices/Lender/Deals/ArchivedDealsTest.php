<?php

declare(strict_types=1);

namespace Tests\Services\QueryServices\Lender\Deals;

use App\Broker;
use App\Deal;
use App\Lender;
use App\Services\QueryServices\Lender\Deals\ArchivedDeals;
use App\Termsheet;
use App\User;
use Database\Seeders\Termsheets;
use Tests\TestCase;

/**
 * Class ArchivedDealsTest
 *
 * @author  Boris Djemrovski <boris@forwardslashny.com>
 */
class ArchivedDealsTest extends TestCase
{
    /** @var Lender */
    private $lender;

    /** @var Deal[] */
    private $archived;

    /** @var Deal[] */
    private $neutral;

    private $testResult;

    /** @var ArchivedDeals */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();
        $this->service = $this->app->make(ArchivedDeals::class);

        $this->lender = Lender::factory()->create([
            'referrer_id' => null,
        ]);

        $broker = Broker::factory()->create([
            'referrer_id' => null,
        ]);
        $this->archived = Deal::factory()->count(9)->create(['user_id' => $broker->id]);
        $this->neutral = Deal::factory()->count(10)->create(['user_id' => $broker->id]);

        $this->archived->each([$this, 'archive']);
    }

    public function testRun()
    {
        $this->testResult = $this->service->run(['lenderId' => $this->lender->id]);

        $this->assertCount(9, $this->testResult);

        $this->archived->each([$this, 'assertContainsDeal']);
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

    public function archive(Deal $deal): Deal
    {
        $this->lender->storeRelationUserDeal($deal->id, User::LENDER_ARCHIVE_DEAL);

        return $deal;
    }
}
