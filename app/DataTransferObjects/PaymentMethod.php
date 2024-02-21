<?php

namespace App\DataTransferObjects;

use App\User;
use Illuminate\Support\Collection;
use Laravel\Cashier\PaymentMethod as StripePayment;

class PaymentMethod
{
    private User $broker;

    public function mapForBroker($paymentMethod, User $broker)
    {
        $this->broker = $broker;

        if ($paymentMethod instanceof Collection) {
            return $paymentMethod->map(fn ($method) => $this->dataUnit($method));
        }

        return $this->dataUnit($paymentMethod);
    }

    private function dataUnit(StripePayment $paymentMethod)
    {
        $stripePayment = $paymentMethod->asStripePaymentMethod();

        return [
            'stripe_payment_id' => $paymentMethod->id,
            'card_type' => $stripePayment->card->brand,
            'exp_year' => $stripePayment->card->exp_year,
            'exp_month' => $stripePayment->card->exp_month,
            'last_4' => $stripePayment->card->last4,
            'default' => $stripePayment->card->last4 === $this->broker->pm_last_four
        ];
    }
}
