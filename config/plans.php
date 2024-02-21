<?php

return [
    'small-monthly' => [
        'name' => 'Small',
        'description' => 'For deals up to $1M.',
        'price' => "250.00",
        'price_id' => env('STRIPE_SMALL_PRICE_ID'),
        'type' => 'monthly',
        'features' => [
            'Access to Lender’s information before accepting the quote.',
            'Meeting rooms (integration with Zoom).',
            'Document sharing. (SOON)'
        ]
    ],
    'small-yearly' => [
        'name' => 'Small',
        'description' => 'For deals up to $1M.',
        'price' => "2,550.00",
        'price_id' => env('STRIPE_SMALL_YEARLY_PRICE_ID'),
        'type' => 'yearly',
        'features' => [
            'Access to Lender’s information before accepting the quote.',
            'Meeting rooms (integration with Zoom).',
            'Document sharing. (SOON)'
        ]
    ],
    'medium' => [
        'name' => 'Medium',
        'description' => 'For deals up to $5M.',
        'price' => "600.00",
        'type' => 'monthly',
        'price_id' => env('STRIPE_MEDIUM_PRICE_ID'),
        'features' => [
            'Access to Lender’s information before accepting the quote.',
            'Meeting rooms (integration with Zoom).',
            'Document sharing. (SOON)'
        ]
    ],
    'medium-yearly' => [
        'name' => 'Medium',
        'description' => 'For deals up to $5M.',
        'price' => "6,120.00",
        'type' => 'yearly',
        'price_id' => env('STRIPE_MEDIUM_YEARLY_PRICE_ID'),
        'features' => [
            'Access to Lender’s information before accepting the quote.',
            'Meeting rooms (integration with Zoom).',
            'Document sharing. (SOON)'
        ]
    ],
    'large' => [
        'name' => 'Large',
        'type' => 'monthly',
        'description' => 'For deals up to $20M.',
        'price' => "1,200.00",
        'price_id' => env('STRIPE_LARGE_PRICE_ID'),
        'features' => [
            'Access to Lender’s information before accepting the quote.',
            'Meeting rooms (integration with Zoom).',
            'Document sharing. (SOON)'
        ]
    ],
    'large-yearly' => [
        'name' => 'Large',
        'type' => 'yearly',
        'description' => 'For deals up to $20M.',
        'price' => "12,240.00",
        'price_id' => env('STRIPE_LARGE_YEARLY_PRICE_ID'),
        'features' => [
            'Access to Lender’s information before accepting the quote.',
            'Meeting rooms (integration with Zoom).',
            'Document sharing. (SOON)'
        ]
    ],
    'extra-large' => [
        'name' => 'Extra Large',
        'type' => 'monthly',
        'description' => 'No $ amount limit.',
        'price' => "2,500.00",
        'price_id' => env('STRIPE_EXTRA_LARGE_PRICE_ID'),
        'features' => [
            'Access to Lender’s information before accepting the quote.',
            'Meeting rooms (integration with Zoom).',
            'Document sharing. (SOON)'
        ]
    ],
    'extra-large-yearly' => [
        'name' => 'Extra Large',
        'type' => 'yearly',
        'description' => 'No $ amount limit.',
        'price' => "25,500.00",
        'price_id' => env('STRIPE_EXTRA_LARGE_YEARLY_PRICE_ID'),
        'features' => [
            'Access to Lender’s information before accepting the quote.',
            'Meeting rooms (integration with Zoom).',
            'Document sharing. (SOON)'
        ]
    ]
];
