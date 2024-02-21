<?php

namespace App\Services\Payment;

use Stripe\Checkout\Session;
use Stripe\Invoice;
use Stripe\Stripe;

class StripePaymentService implements PaymentInterface
{
    public function retrieveCheckout(mixed $stripe_checkout_id)
    {
        Stripe::setApiKey(config('stripe.secret'));

        return Session::retrieve($stripe_checkout_id);
    }

    public function createCheckout(array $payload)
    {
        Stripe::setApiKey(config('stripe.secret'));

        return Session::create($payload);
    }

    public function nextBillingDate(string $customer_id)
    {
        $nextBillingAttempt = $this->upcomingInvoice($customer_id)->next_payment_attempt;
        return \Carbon\Carbon::parse($nextBillingAttempt)->format('Y-m-d');
    }

    public function nextBillingAmount(string $customer_id)
    {
        $amount = $this->upcomingInvoice($customer_id)->amount_due;

        return $amount /100;
    }

    private function upcomingInvoice(string $customer_id): Invoice
    {
        Stripe::setApiKey(config('stripe.secret'));
        return Invoice::upcoming([
            'customer' => $customer_id
        ]);
    }
}
