<?php

namespace App\Enums;

enum DealPurchaseType: int
{
    case NOT_PURCHASED = 0;
    case PURCHASED_AS_PAY_PER_DEAL = 1;
    case PURCHASED_VIA_SUBSCRIPTION = 2;
    case NOT_PURCHASED_FREE = 3; // finished before we started charging

    public function slug(): string
    {
        return match ($this) {
            self::NOT_PURCHASED => 'not-purchased',
            self::PURCHASED_AS_PAY_PER_DEAL => 'pay-per-deal',
            self::PURCHASED_VIA_SUBSCRIPTION => 'subscription',
            self::NOT_PURCHASED_FREE => 'free',
        };
    }

    public function dealType(): string
    {
        return match ($this) {
            self::NOT_PURCHASED => 'Limited Deal',
            self::PURCHASED_AS_PAY_PER_DEAL => 'Pay-per-deal Premium',
            self::PURCHASED_VIA_SUBSCRIPTION => 'Subscription Premium',
            self::NOT_PURCHASED_FREE => 'Free Premium',
        };
    }
}
