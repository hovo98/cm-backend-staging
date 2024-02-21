<?php

namespace App\DataTransferObjects;

use App\Deal;
use Laravel\Cashier\Subscription;

class Plan
{
    public const SMALL_LOAN_LIMIT = 1000000;
    public const MEDIUM_LOAN_LIMIT = 5000000;
    public const LARGE_LOAN_LIMIT = 20000000;

    public const SMALL = 'starter';
    public const MEDIUM = 'advanced';
    public const LARGE = 'professional';
    public const EXTRA_LARGE = 'unlimited';

    private $plan;

    public function from(string $priceId)
    {
        $plans = [
            env('STRIPE_SMALL_PRICE_ID') => self::SMALL,

            env('STRIPE_SMALL_YEARLY_PRICE_ID') => self::SMALL,

            env('STRIPE_MEDIUM_PRICE_ID') => self::MEDIUM,

            env('STRIPE_MEDIUM_YEARLY_PRICE_ID') => self::MEDIUM,

            env('STRIPE_LARGE_PRICE_ID') => self::LARGE,

            env('STRIPE_LARGE_YEARLY_PRICE_ID') => self::LARGE,

            env('STRIPE_EXTRA_LARGE_PRICE_ID') => self::EXTRA_LARGE,

            env('STRIPE_EXTRA_LARGE_YEARLY_PRICE_ID') =>self::EXTRA_LARGE,
        ];

        $this->plan = data_get($plans, $priceId);

        return $this;
    }

    public static function fromSubscription(Subscription $subscription)
    {
        return (new static())->from($subscription->stripe_price);
    }

    public function isValidAmount($dollarAmount): bool
    {
        if ($this->plan === self::EXTRA_LARGE) {
            return true;
        }

        if ($this->plan === self::SMALL && $dollarAmount <= self::SMALL_LOAN_LIMIT) {
            return true;
        }

        if ($this->plan === self::MEDIUM && $dollarAmount <= self::MEDIUM_LOAN_LIMIT) {
            return true;
        }

        if ($this->plan === self::LARGE && $dollarAmount <= self::LARGE_LOAN_LIMIT) {
            return true;
        }

        return false;
    }

    public function determinePriceId($deal): string
    {
        $dollarAmount = $deal->getDollarAmount();

        if ($dollarAmount >= self::LARGE_LOAN_LIMIT) {
            return config('stripe.extra_large_one_time_price');
        }

        if ($dollarAmount <= self::SMALL_LOAN_LIMIT) {
            return config('stripe.small_one_time_price');
        }

        if ($dollarAmount <= self::MEDIUM_LOAN_LIMIT) {
            return config('stripe.medium_one_time_price');
        }

        return config('stripe.large_one_time_price');
    }

    public function getPrice(Deal $deal)
    {
        $dollarAmount = $deal->getDollarAmount();

        if ($dollarAmount >= self::LARGE_LOAN_LIMIT) {
            return 750;
        }

        if ($dollarAmount <= self::SMALL_LOAN_LIMIT) {
            return 150;
        }

        if ($dollarAmount <= self::MEDIUM_LOAN_LIMIT) {
            return 375;
        }

        return 1500;

    }

    public function getDollarValueForPrice($priceId)
    {
        $plans = [
            env('STRIPE_SMALL_PRICE_ID') => 250,

            env('STRIPE_SMALL_YEARLY_PRICE_ID') => 250,

            env('STRIPE_MEDIUM_PRICE_ID') => 600,

            env('STRIPE_MEDIUM_YEARLY_PRICE_ID') => 600,

            env('STRIPE_LARGE_PRICE_ID') => 1200,

            env('STRIPE_LARGE_YEARLY_PRICE_ID') => 1200,

            env('STRIPE_EXTRA_LARGE_PRICE_ID') => 2500,

            env('STRIPE_EXTRA_LARGE_YEARLY_PRICE_ID') => 25000,
        ];

        return data_get($plans, $priceId);
    }

    public function getPlanName(string $priceId)
    {
        $plans = [
            env('STRIPE_SMALL_PRICE_ID') => self::SMALL,

            env('STRIPE_SMALL_YEARLY_PRICE_ID') => self::SMALL,

            env('STRIPE_MEDIUM_PRICE_ID') => self::MEDIUM,

            env('STRIPE_MEDIUM_YEARLY_PRICE_ID') => self::MEDIUM,

            env('STRIPE_LARGE_PRICE_ID') => self::LARGE,

            env('STRIPE_LARGE_YEARLY_PRICE_ID') => self::LARGE,

            env('STRIPE_EXTRA_LARGE_PRICE_ID') => self::EXTRA_LARGE,

            env('STRIPE_EXTRA_LARGE_YEARLY_PRICE_ID') =>self::EXTRA_LARGE,
        ];

        return ucwords(str_replace('-', ' ', data_get($plans, $priceId)));
    }
}
