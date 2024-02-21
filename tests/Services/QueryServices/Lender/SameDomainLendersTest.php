<?php

declare(strict_types=1);

namespace Tests\Services\QueryServices\Lender;

use App\Lender;
use App\Services\QueryServices\Lender\SameDomainLenders;
use Tests\TestCase;

/**
 * Class SameDomainLendersTest
 *
 * @author  Boris Djemrovski <boris@forwardslashny.com>
 */
class SameDomainLendersTest extends TestCase
{
    /** @var Lender */
    private $mainLender;

    /** @var Lender */
    private $lender1;

    /** @var Lender */
    private $lender2;

    /** @var Lender */
    private $lender3;

    /** @var SameDomainLenders */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(SameDomainLenders::class);

        $this->mainLender = Lender::factory()->create(['email' => 'email@domain.com', 'referrer_id' => null]);
        $this->lender1 = Lender::factory()->create(['email' => 'email1@domain.com', 'referrer_id' => null]);
        $this->lender2 = Lender::factory()->create(['email' => 'email2@domain.com', 'referrer_id' => null]);
        $this->lender3 = Lender::factory()->create(['email' => 'newemail@testing.com', 'referrer_id' => null]);
    }

    public function testRun()
    {
        $result = $this->service->run(['id' => $this->mainLender->id, 'domain' => $this->mainLender->domain]);

        $this->assertContains($this->lender1->id, $result);
        $this->assertContains($this->lender2->id, $result);
        $this->assertNotContains($this->lender3->id, $result);
    }
}
