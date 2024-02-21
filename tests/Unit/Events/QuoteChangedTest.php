<?php

namespace Tests\Unit\Events;

use App\Broker;
use App\Deal;
use App\Events\QuoteRejected;
use App\Lender;
use App\Notifications\UnacceptedQuoteLender;
use App\Quote;
use App\Termsheet;
use Database\Seeders\Termsheets;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class QuoteChangedTest extends TestCase
{
    /**
     * @return void
     * @test
     */
    public function rejecting_a_quote_removes_quote_limit_block()
    {
        Notification::fake();

        $broker = Broker::factory()->create();
        $lender = Lender::factory()->create();
        $this->seed(Termsheets::class);
        $termsheet = Termsheet::where('title', 'Open')->first();
        $termsheet->id = 1;
        $termsheet->save();

        $deal = Deal::factory()->for($broker)->dealLimitReached()->create();
        $quote = Quote::factory()->for($deal)->for($lender)->create();

        event(new QuoteRejected($quote));

        Notification::assertSentTo($lender, UnacceptedQuoteLender::class);
        $this->assertFalse($deal->refresh()->quoteLimitReached());
    }
}
