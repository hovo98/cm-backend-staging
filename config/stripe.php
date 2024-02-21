<?php

return [

    'secret' => env('STRIPE_SECRET'),

    'small_one_time_price' => env('STRIPE_SMALL_ONE_TIME_PRICE_ID'),

    'medium_one_time_price' => env('STRIPE_MEDIUM_ONE_TIME_PRICE_ID'),

    'large_one_time_price' => env('STRIPE_LARGE_ONE_TIME_PRICE_ID'),

    'extra_large_one_time_price' => env('STRIPE_EXTRA_LARGE_ONE_TIME_PRICE_ID'),

    'small_monthly' => env('STRIPE_SMALL_PRICE_ID'),

    'small_yearly' =>env('STRIPE_SMALL_YEARLY_PRICE_ID'),

    'medium_monthly' => env('STRIPE_MEDIUM_PRICE_ID'),

    'medium_yearly' => env('STRIPE_MEDIUM_YEARLY_PRICE_ID'),

    'large_monthly' => env('STRIPE_LARGE_PRICE_ID'),

    'large_yearly' => env('STRIPE_LARGE_YEARLY_PRICE_ID'),

    'extra_large_monthly' => env('STRIPE_EXTRA_LARGE_PRICE_ID'),

    'extra_large_yearly' => env('STRIPE_EXTRA_LARGE_YEARLY_PRICE_ID'),
];
