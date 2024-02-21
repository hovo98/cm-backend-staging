<?php

namespace Database\Factories;

use App\Deal;
use App\Payment;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\AppPayment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'deal_id' => Deal::factory(),
            'user_id' => User::factory(),
            'stripe_checkout_id' => Str::uuid()->toString(),
            'payment_status' => Payment::STATUS_UNPAID
        ];
    }

    /**
     * @return PaymentFactory
     */
    public function unpaid()
    {
        return $this->state([
            'payment_status' => Payment::STATUS_UNPAID,
        ]);
    }

    /**
     * @return PaymentFactory
     */
    public function paid()
    {
        return $this->state([
            'payment_status' => Payment::STATUS_PAID
        ]);
    }

    /**
     * @return PaymentFactory
     */
    public function small()
    {
        return $this->state([
            'stripe_price_id' => config('stripe.small_one_time_price')
        ]);
    }

    /**
     * @return PaymentFactory
     */
    public function medium()
    {
        return $this->state([
            'stripe_price_id' => config('stripe.medium_one_time_price')
        ]);
    }

    /**
     * @return PaymentFactory
     */
    public function large()
    {
        return $this->state([
            'stripe_price_id' => config('stripe.large_one_time_price')
        ]);
    }

    /**
     * @return PaymentFactory
     */
    public function extraLarge()
    {
        return $this->state([
            'stripe_price_id' => config('stripe.extra_large_one_time_price')
        ]);
    }
}
