<?php

declare(strict_types=1);

namespace Tests\Services\QueryServices\Lender\Brokers;

use App\Broker;
use App\Lender;
use App\Services\QueryServices\Lender\Brokers\BrokersConnectedToMultipleLenders;
use Tests\TestCase;

/**
 * Class BrokersConnectedToMultipleLendersTest
 *
 * @author  Boris Djemrovski <boris@forwardslashny.com>
 */
class BrokersConnectedToMultipleLendersTest extends TestCase
{
    /** @var Lender[] */
    private $lenders;

    /** @var Broker[] */
    private $selectedBrokers;

    /** @var Broker[] */
    private $notSelectedBrokers;

    /** @var BrokersConnectedToMultipleLenders */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(BrokersConnectedToMultipleLenders::class);

        // Create Lenders
        $this->lenders = Lender::factory()->count(20)->create([
            'referrer_id' => null,
        ]);

        // Create Brokers
        $brokers = Broker::factory()->count(20)->create([
            'referrer_id' => null,
        ]);

        // Loop through 5 random and connect them to the Lenders
        $this->selectedBrokers = $brokers->random(5);
        $this->selectedBrokers->each(function ($broker) {
            $broker->lenders()->attach($this->lenders->random(2));
        });

        // Get the not selected Brokers
        $this->notSelectedBrokers = $brokers->diff($this->selectedBrokers);
    }

    public function testRun()
    {
        // Get the IN array
        $in = $this->lenders->pluck('id')->toArray();

        // Run the query
        $result = $this->service->run(['in' => $in]);

        $this->assertCount(5, $result);

        // Loop through selected random Brokers and check if all of them are in the results
        $this->selectedBrokers->each(function ($broker) use ($result) {
            $this->assertContains($broker->id, $result);
        });

        // Loop through not selected Brokers and check if they are not in the results
        $this->notSelectedBrokers->each(function ($broker) use ($result) {
            $this->assertNotContains($broker->id, $result);
        });
    }
}
