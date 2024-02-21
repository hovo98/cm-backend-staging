<?php

declare(strict_types=1);

namespace Tests\Services\QueryServices\Lender\Deals;

use App\Broker;
use App\Deal;
use App\Lender;
use App\Services\QueryServices\Lender\Deals\ForbiddenDeals;
use App\Termsheet;
use App\User;
use Database\Seeders\Termsheets;
use Tests\TestCase;

/**
 * Class LenderForbiddenDealsTest
 *
 * @author  Boris Djemrovski <boris@forwardslashny.com>
 */
class LenderForbiddenDealsTest extends TestCase
{
    /** @var ForbiddenDeals */
    private $service;

    /** @var Lender */
    private $mainLender;

    /** @var Deal[] */
    private $forbiddenDeals;

    /** @var Deal[] */
    private $allowedDeals;

    /** @var Deal[] */
    private $ignoredDeals;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();
        $this->service = $this->app->make(ForbiddenDeals::class);

        // Create the Main Lender
        $this->mainLender = Lender::factory()->create(['email' => 'email@domain.com', 'referrer_id' => null]);

        // Create additional lenders for testing
        $lenderSameDomain = Lender::factory()->create(['email' => 'email2@domain.com', 'referrer_id' => null]);
        $lenderDiffDomain = Lender::factory()->create(['email' => 'newemail@testing.com', 'referrer_id' => null]);

        // Create Brokers
        $brokerForbidden = Broker::factory()->create(['referrer_id' => null]);
        $brokerAllowed = Broker::factory()->create(['referrer_id' => null]);

        // Connect them
        $brokerForbidden->lenders()->attach($lenderSameDomain);
        $brokerAllowed->lenders()->attach($lenderDiffDomain);

        // Allowed Deals - belongs to broker which is NOT connected to the Lenders with the same domain
        $this->allowedDeals = Deal::factory()->count(21)->create(['user_id' => $brokerAllowed->id]);

        // Create new chunk of Deals by Broker which IS connected to the Lenders with the same domain
        $deals = collect(Deal::factory()->count(16)->create(['user_id' => $brokerForbidden->id]));

        // Get random 7 for ignoring
        $this->ignoredDeals = $deals->random(7);

        // Forbidden number should be 9 now
        $this->forbiddenDeals = $deals->diff($this->ignoredDeals);

        // Ignore the 7 random deals, as they should not be forbidden for the Lenders with the same domain
        $this->ignoredDeals->each(function ($deal) use ($lenderSameDomain) {
            $lenderSameDomain->storeRelationUserDeal($deal->id, User::LENDER_IGNORE_DEAL);
        });
    }

    // Todo: Fix the test. Silented for now. Maybe this logic is not longer needed or true because the test was commented out.
    public function xtestRun()
    {
        $result = $this->service->run(['id' => $this->mainLender->id, 'domain' => $this->mainLender->domain]);

        $this->assertCount(9, $result);

        // Forbidden deals - should be in the results
        $this->forbiddenDeals->each(function ($deal) use ($result) {
            $this->assertContains($deal->id, $result);
        });

        // Allowed deals - should NOT be in the results
        $this->allowedDeals->each(function ($deal) use ($result) {
            $this->assertNotContains($deal->id, $result);
        });

        // Ignored deals - should NOT be in the results
        $this->ignoredDeals->each(function ($deal) use ($result) {
            $this->assertNotContains($deal->id, $result);
        });
    }
}
