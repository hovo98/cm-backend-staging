<?php

namespace App\Jobs;

use App\Events\DealPurchased;
use App\Payment;
use App\StripeWebhook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompleteOneTimePayment implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public StripeWebhook $webhook)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $checkoutId = data_get($this->webhook->payload, 'data.object.id');
        $status = data_get($this->webhook->payload, 'data.object.payment_status');

        /** @var Payment $payment */
        $payment = Payment::where('stripe_checkout_id', $checkoutId)->first();

        if ($payment) {
            $payment->update([
                'payment_status' => $status,
                'processed' => true
            ]);

            if ($payment->isComplete()) {
                DealPurchased::dispatch($payment);
            }
        }
    }
}
