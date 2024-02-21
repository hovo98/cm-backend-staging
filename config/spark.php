<?php

use App\User;
use Spark\Features;

return [

    /*
    |--------------------------------------------------------------------------
    | Spark Path
    |--------------------------------------------------------------------------
    |
    | This configuration option determines the URI at which the Spark billing
    | portal is available. You are free to change this URI to a value that
    | you prefer. You shall link to this location from your application.
    |
    */

    'path' => 'billing',

    /*
    |--------------------------------------------------------------------------
    | Spark Middleware
    |--------------------------------------------------------------------------
    |
    | These are the middleware that requests to the Spark billing portal must
    | pass through before being accepted. Typically, the default list that
    | is defined below should be suitable for most Laravel applications.
    |
    */

    'dashboard_url' => env('FRONTEND_URL') . '/',


    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Branding
    |--------------------------------------------------------------------------
    |
    | These configuration values allow you to customize the branding of the
    | billing portal, including the primary color and the logo that will
    | be displayed within the billing portal. This logo value must be
    | the absolute path to an SVG logo within the local filesystem.
    |
    */

    // 'brand' =>  [
    //     'logo' => realpath(__DIR__.'/../public/svg/billing-logo.svg'),
    //     'color' => 'bg-gray-800',
    // ],

    /*
    |--------------------------------------------------------------------------
    | Proration Behavior
    |--------------------------------------------------------------------------
    |
    | This value determines if charges are prorated when making adjustments
    | to a plan such as incrementing or decrementing the quantity of the
    | plan. This also determines proration behavior if changing plans.
    |
    */

    'prorates' => true,

    /*
    |--------------------------------------------------------------------------
    | Spark Date Format
    |--------------------------------------------------------------------------
    |
    | This date format will be utilized by Spark to format dates in various
    | locations within the billing portal, such as while showing invoice
    | dates. You should customize the format based on your own locale.
    |
    */

    'date_format' => 'F j, Y',

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Some of Spark's features are optional. You may disable the features by
    | removing them from this array. By removing features from this array
    | you can customize your Spark experience for your own application.
    |
    */

    'features' => [
        // Features::billingAddressCollection(['required' => true]),
        // Features::mustAcceptTerms(),
        // Features::euVatCollection(['home-country' => 'BE']),
        // Features::receiptEmails(['custom-addresses' => true]),
        Features::paymentNotificationEmails(),
    ],

    /*
    |--------------------------------------------------------------------------
    | Receipt Configuration
    |--------------------------------------------------------------------------
    |
    | The following configuration options allow you to configure the data that
    | appears in PDF receipts generated by Spark. Typically, this data will
    | include a company name as well as your company contact information.
    |
    */

    'receipt_data' => [
        'vendor' => 'Your Product',
        'product' => 'Your Product',
        'street' => '111 Example St.',
        'location' => 'Los Angeles, CA',
        'phone' => '555-555-5555',
    ],

    /*
    |--------------------------------------------------------------------------
    | Spark Billable
    |--------------------------------------------------------------------------
    |
    | Below you may define billable entities supported by your Spark driven
    | application. The Stripe edition of Spark currently only supports a
    | single billable model entity (team, user, etc.) per application.
    |
    | In addition to defining your billable entity, you may also define its
    | plans and the plan's features, including a short description of it
    | as well as a "bullet point" listing of its distinctive features.
    |
    */

    'billables' => [

        'user' => [
            'model' => User::class,
            'trial_days' => 5,
            'default_interval' => 'monthly',
            'plans' => [
                [
                    'name' => 'Starter',
                    'short_description' => 'For deals up to $1M.',
                    'monthly_id' => env('STRIPE_SMALL_PRICE_ID'),
                    'yearly_id' => env('STRIPE_SMALL_YEARLY_PRICE_ID'),
                    'yearly_incentive' => 'Save 15%',
                    'features' => [
                        'For deals up to $1M.',
                        'Access to Lender’s information before accepting the quote.',
                        'Meeting rooms (integration with Zoom).',
                        'Document sharing. (SOON)'
                    ],
                ],
                [
                    'name' => 'Advanced',
                    'short_description' => 'For deals up to $5M.',
                    'monthly_id' => env('STRIPE_MEDIUM_PRICE_ID'),
                    'yearly_id' => env('STRIPE_MEDIUM_YEARLY_PRICE_ID'),
                    'yearly_incentive' => 'Save 15%',
                    'features' => [
                        'For deals up to $5M.',
                        'Access to Lender’s information before accepting the quote.',
                        'Meeting rooms (integration with Zoom).',
                        'Document sharing. (SOON)'
                    ],
                ],
                [
                    'name' => 'Professional',
                    'short_description' => 'For deals up to $20M.',
                    'monthly_id' => env('STRIPE_LARGE_PRICE_ID'),
                    'yearly_id' => env('STRIPE_LARGE_YEARLY_PRICE_ID'),
                    'yearly_incentive' => 'Save 15%',
                    'features' => [
                        'For deals up to $20M.',
                        'Access to Lender’s information before accepting the quote.',
                        'Meeting rooms (integration with Zoom).',
                        'Document sharing. (SOON)'
                    ],
                ],
                [
                    'name' => 'Unlimited',
                    'short_description' => 'No $ amount limit.',
                    'monthly_id' => env('STRIPE_EXTRA_LARGE_PRICE_ID'),
                    'yearly_id' => env('STRIPE_EXTRA_LARGE_YEARLY_PRICE_ID'),
                    'yearly_incentive' => 'Save 15%',
                    'features' => [
                        'No $ amount limit.',
                        'Access to Lender’s information before accepting the quote.',
                        'Meeting rooms (integration with Zoom).',
                        'Document sharing. (SOON)'
                    ],
                ],
            ],
        ],
    ],
];
