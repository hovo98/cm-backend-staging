<?php

namespace Tests\Emails;

use App\Deal;
use App\Broker;
use App\Lender;
use App\Quote;
use App\Notifications\QuoteDeal;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteDealEmailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function a_broker_on_a_limited_tier_does_not_see_the_bank_name_in_a_quote_email(): void
    {
        Notification::fake();

        $broker = Broker::factory()->withCompany()->create();

        $this->setupTermSheets();

        $deal = Deal::factory()->for($broker)->published()->create();

        $lender = Lender::factory()->withCompany()->linkedDeal($deal)->create();

        $quote = Quote::factory()->for($deal)->for($lender)->create();

        $broker->notify(new QuoteDeal(
            loan_type: $deal->getLoanType(),
            lender: $lender->name(),
            quote_id: $quote->id,
            deal_id: $deal->id,
            lender_id: $lender->id,
            message: $quote->data['message'],
            streetName: $deal->data['location']['street_address']
        ));

        // Assert
        Notification::assertSentTo($broker, QuoteDeal::class, function ($notification, $channels) use ($broker) {
            $mailData = $notification->toMail($broker);

            $this->assertStringContainsString('You’ve received a quote from a Lender!', $mailData->render());
            $this->assertStringContainsString('Message from the Lender', $mailData->render());

            return true;
        });
    }

    /**
     * @test
     */
    public function a_broker_that_paid_for_a_deal_can_see_the_bank_name_in_the_quote_notification_email(): void
    {
        Notification::fake();

        $broker = Broker::factory()->withCompany()->create();

        $this->setupTermSheets();

        $deal = Deal::factory()->for($broker)->published()->purchased()->create();

        $lender = Lender::factory()->withCompany()->linkedDeal($deal)->create();
        $lender->company->update(['company_name' => 'Jons Amazing Bank']);

        $quote = Quote::factory()->for($deal)->for($lender)->create();

        $broker->notify(new QuoteDeal(
            loan_type: $deal->getLoanType(),
            lender: $lender->name(),
            quote_id: $quote->id,
            deal_id: $deal->id,
            lender_id: $lender->id,
            message: $quote->data['message'],
            streetName: $deal->data['location']['street_address']
        ));

        // Assert
        Notification::assertSentTo($broker, QuoteDeal::class, function ($notification, $channels) use ($broker) {
            $mailData = $notification->toMail($broker);

            $this->assertStringContainsString('You’ve received a quote from Jons Amazing Bank!', $mailData->render());
            $this->assertStringContainsString('Message from Jons Amazing Bank', $mailData->render());

            return true;
        });
    }
}
