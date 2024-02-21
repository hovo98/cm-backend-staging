<?php

namespace App\Services\Payment;

use Illuminate\Support\Str;

class DummyStripeService implements PaymentInterface
{
    public function retrieveCheckout(mixed $stripe_checkout_id)
    {
        $mock = new \stdClass();

        $mock->payment_status = 'paid';

        return $mock;
    }

    public function createCheckout(array $array_merge)
    {
        $mock = new \stdClass();

        $mock->id = Str::uuid()->toString();
        $mock->expires_at = time();
        $mock->url = "https://checkout.financelobby.com/c/pay/cs_test_a1CWJAGbP0DWQgq0ZcJ2G7RXekBFVynzanL9MAEMFrIURUGt0CTy0dPdJj#fidkdWxOYHwnPyd1blpxYHZxWjA0SGAwRjREfHRgSjxMcklyVF1xXUBGNTBQZElUbU1AbmNzdXN%2Fc0hGRkJDRHNLcmNsMEFrfXNtVjNRUXxhSHNtZn9BZks0MlxOUlUzdW1jaExOYjJ8MGZKNTV8cmJOfHJGMycpJ2N3amhWYHdzYHcnP3F3cGApJ2lkfGpwcVF8dWAnPyd2bGtiaWBabHFgaCcpJ2BrZGdpYFVpZGZgbWppYWB3dic%2FcXdwYHgl";
        $mock->payment_status = 'upaid';

        return $mock;
    }

    public function nextBillingDate(string $cutomer_id = null)
    {
        return now()->addMonth()->toDateString();
    }

    public function nextBillingAmount(string $customer_id = null)
    {
        return 2500;
    }
}
