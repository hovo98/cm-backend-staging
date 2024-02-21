<?php

namespace Tests\Mocks\Stripe;

use Illuminate\Support\Arr;

class PaymentSucceededEvent
{
    public array $attributes;

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function __toString()
    {
        return self::get($this->attributes);
    }

    public static function get(array $attributes)
    {
        return [
            'id' => 'evt_1MxukFAyqeO9IwLwWqxrjmzy',
            'object' => 'event',
            'api_version' => '2022-11-15',
            'created' => 1681748254,
            'data' => [
                'object' => [
                    'id' => 'in_1MxukCAyqeO9IwLwE5sYhZWH',
                    'object' => 'invoice',
                    'account_country' => 'US',
                    'account_name' => 'Finance Lobby LLC',
                    'account_tax_ids' => null,
                    'amount_due' => 25000,
                    'amount_paid' => 25000,
                    'amount_remaining' => 0,
                    'amount_shipping' => 0,
                    'application' => null,
                    'application_fee_amount' => null,
                    'attempt_count' => 1,
                    'attempted' => true,
                    'auto_advance' => false,
                    'automatic_tax' => [
                        'enabled' => false,
                        'status' => null
                    ],
                    'billing_reason' => 'subscription_create',
                    'charge' => 'ch_3MxukCAyqeO9IwLw1I40eoKl',
                    'collection_method' => 'charge_automatically',
                    'created' => 1681748252,
                    'currency' => 'usd',
                    'custom_fields' => null,
                    'customer' => 'cus_NjNUw9YxJbWD0K',
                    'customer_address' => [
                        'city' => '',
                        'country' => '',
                        'line1' => '',
                        'line2' => '',
                        'postal_code' => '',
                        'state' => ''
                    ],
                    'customer_email' => Arr::get($attributes, 'email', 'carlos.pruitt@usbank.com'),
                    'customer_name' => 'Carlos Pruitt',
                    'customer_phone' => null,
                    'customer_shipping' => null,
                    'customer_tax_exempt' => 'none',
                    'customer_tax_ids' => [

                    ],
                    'default_payment_method' => null,
                    'default_source' => null,
                    'default_tax_rates' => [

                    ],
                    'description' => null,
                    'discount' => null,
                    'discounts' => [

                    ],
                    'due_date' => null,
                    'ending_balance' => 0,
                    'footer' => null,
                    'from_invoice' => null,
                    'hosted_invoice_url' => 'https://invoice.stripe.com/i/acct_1Me5C1AyqeO9IwLw/test_YWNjdF8xTWU1QzFBeXFlTzlJd0x3LF9Oak5WZ0RZeEpVUFhrTmI1VmlGbnc2WG53TG1wNEF3LDcyMjg5MDU10200iiQeG6m7?s=ap',
                    'invoice_pdf' => 'https://pay.stripe.com/invoice/acct_1Me5C1AyqeO9IwLw/test_YWNjdF8xTWU1QzFBeXFlTzlJd0x3LF9Oak5WZ0RZeEpVUFhrTmI1VmlGbnc2WG53TG1wNEF3LDcyMjg5MDU10200iiQeG6m7/pdf?s=ap',
                    'last_finalization_error' => null,
                    'latest_revision' => null,
                    'lines' => [
                        'object' => 'list',
                        'data' => [
                            [
                                'id' => 'il_1MxukCAyqeO9IwLwAF98su71',
                                'object' => 'line_item',
                                'amount' => 25000,
                                'amount_excluding_tax' => 25000,
                                'currency' => 'usd',
                                'description' => '1 \u00d7 Small (at $250.00 / month)',
                                'discount_amounts' => [

                                ],
                                'discountable' => true,
                                'discounts' => [

                                ],
                                'livemode' => false,
                                'metadata' => [
                                    'name' => 'default'
                                ],
                                'period' => [
                                    'end' => 1684340252,
                                    'start' => 1681748252
                                ],
                                'plan' => [
                                    'id' => 'price_1MtXiVAyqeO9IwLwExBohum0',
                                    'object' => 'plan',
                                    'active' => true,
                                    'aggregate_usage' => null,
                                    'amount' => 25000,
                                    'amount_decimal' => '25000',
                                    'billing_scheme' => 'per_unit',
                                    'created' => 1680706423,
                                    'currency' => 'usd',
                                    'interval' => 'month',
                                    'interval_count' => 1,
                                    'livemode' => false,
                                    'metadata' => [

                                    ],
                                    'nickname' => null,
                                    'product' => 'prod_NerRxl8GThmNbr',
                                    'tiers_mode' => null,
                                    'transform_usage' => null,
                                    'trial_period_days' => null,
                                    'usage_type' => 'licensed'
                                ],
                                'price' => [
                                    'id' => 'price_1MtXiVAyqeO9IwLwExBohum0',
                                    'object' => 'price',
                                    'active' => true,
                                    'billing_scheme' => 'per_unit',
                                    'created' => 1680706423,
                                    'currency' => 'usd',
                                    'custom_unit_amount' => null,
                                    'livemode' => false,
                                    'lookup_key' => null,
                                    'metadata' => [

                                    ],
                                    'nickname' => null,
                                    'product' => 'prod_NerRxl8GThmNbr',
                                    'recurring' => [
                                        'aggregate_usage' => null,
                                        'interval' => 'month',
                                        'interval_count' => 1,
                                        'trial_period_days' => null,
                                        'usage_type' => 'licensed'
                                    ],
                                    'tax_behavior' => 'unspecified',
                                    'tiers_mode' => null,
                                    'transform_quantity' => null,
                                    'type' => 'recurring',
                                    'unit_amount' => 25000,
                                    'unit_amount_decimal' => '25000'
                                ],
                                'proration' => false,
                                'proration_details' => [
                                    'credited_items' => null
                                ],
                                'quantity' => 1,
                                'subscription' => 'sub_1MxukCAyqeO9IwLwm7h5xfep',
                                'subscription_item' => 'si_NjNVvyh8baFu0r',
                                'tax_amounts' => [

                                ],
                                'tax_rates' => [

                                ],
                                'type' => 'subscription',
                                'unit_amount_excluding_tax' => '25000'
                            ]
                        ],
                        'has_more' => false,
                        'total_count' => 1,
                        'url' => '/v1/invoices/in_1MxukCAyqeO9IwLwE5sYhZWH/lines'
                    ],
                    'livemode' => false,
                    'metadata' => [

                    ],
                    'next_payment_attempt' => null,
                    'number' => '3ED33366-0001',
                    'on_behalf_of' => null,
                    'paid' => true,
                    'paid_out_of_band' => false,
                    'payment_intent' => 'pi_3MxukCAyqeO9IwLw1gB5X3yP',
                    'payment_settings' => [
                        'default_mandate' => null,
                        'payment_method_options' => null,
                        'payment_method_types' => null
                    ],
                    'period_end' => 1681748252,
                    'period_start' => 1681748252,
                    'post_payment_credit_notes_amount' => 0,
                    'pre_payment_credit_notes_amount' => 0,
                    'quote' => null,
                    'receipt_number' => null,
                    'rendering_options' => null,
                    'shipping_cost' => null,
                    'shipping_details' => null,
                    'starting_balance' => 0,
                    'statement_descriptor' => null,
                    'status' => 'paid',
                    'status_transitions' => [
                        'finalized_at' => 1681748252,
                        'marked_uncollectible_at' => null,
                        'paid_at' => 1681748254,
                        'voided_at' => null
                    ],
                    'subscription' => 'sub_1MxukCAyqeO9IwLwm7h5xfep',
                    'subtotal' => 25000,
                    'subtotal_excluding_tax' => 25000,
                    'tax' => null,
                    'test_clock' => null,
                    'total' => 25000,
                    'total_discount_amounts' => [

                    ],
                    'total_excluding_tax' => 25000,
                    'total_tax_amounts' => [

                    ],
                    'transfer_data' => null,
                    'webhooks_delivered_at' => null
                ]
            ],
            'livemode' => false,
            'pending_webhooks' => 4,
            'request' => [
                'id' => 'req_xUb1tDvNj6YlwT',
                'idempotency_key' => '3e075542-d41f-4743-a9db-aef2c9bb4bf7'
            ],
            'type' => 'invoice.payment_succeeded'
        ];
    }
}
