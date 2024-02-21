<?php

namespace App\Services\Payment;

interface PaymentInterface
{
    public function retrieveCheckout(mixed $stripe_checkout_id);

    public function createCheckout(array $array_merge);

    public function nextBillingDate(string $customer_id);

    public function nextBillingAmount(string $customer_id);
}
