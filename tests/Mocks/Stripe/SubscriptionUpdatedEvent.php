<?php

namespace Tests\Mocks\Stripe;

class SubscriptionUpdatedEvent
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

    public static function get(array $attributes = [])
    {
        return array_merge([
            "id" => "evt_1N7l1AAyqeO9IwLwS7ZjGDLa",
            "object" => "event",
            "api_version" => "2022-11-15",
            "created" => 1684094144,
            "data" => [
                "object" => [
                    "id" => "sub_1N7kpCAyqeO9IwLwSOoUyZaO",
                    "object" => "subscription",
                    "application" => null,
                    "application_fee_percent" => null,
                    "automatic_tax" => [
                        "enabled" => false
                    ],
                    "billing_cycle_anchor" => 1684093402,
                    "billing_thresholds" => null,
                    "cancel_at" => 1686771802,
                    "cancel_at_period_end" => true,
                    "canceled_at" => 1684094144,
                    "cancellation_details" => [
                        "comment" => null,
                        "feedback" => null,
                        "reason" => "cancellation_requested"
                    ],
                    "collection_method" => "charge_automatically",
                    "created" => 1684093402,
                    "currency" => "usd",
                    "current_period_end" => 1686771802,
                    "current_period_start" => 1684093402,
                    "customer" => "cus_NtXu5qvDoTiNXp",
                    "days_until_due" => null,
                    "default_payment_method" => null,
                    "default_source" => null,
                    "default_tax_rates" => [
                    ],
                    "description" => null,
                    "discount" => null,
                    "ended_at" => null,
                    "items" => [
                        "object" => "list",
                        "data" => [
                            [
                                "id" => "si_NtXxrFeypSuZ4c",
                                "object" => "subscription_item",
                                "billing_thresholds" => null,
                                "created" => 1684093541,
                                "metadata" => [
                                ],
                                "plan" => [
                                    "id" => "price_1MtXiVAyqeO9IwLwExBohum0",
                                    "object" => "plan",
                                    "active" => true,
                                    "aggregate_usage" => null,
                                    "amount" => 25000,
                                    "amount_decimal" => "25000",
                                    "billing_scheme" => "per_unit",
                                    "created" => 1680706423,
                                    "currency" => "usd",
                                    "interval" => "month",
                                    "interval_count" => 1,
                                    "livemode" => false,
                                    "metadata" => [
                                    ],
                                    "nickname" => null,
                                    "product" => "prod_NerRxl8GThmNbr",
                                    "tiers_mode" => null,
                                    "transform_usage" => null,
                                    "trial_period_days" => null,
                                    "usage_type" => "licensed"
                                ],
                                "price" => [
                                    "id" => "price_1MtXiVAyqeO9IwLwExBohum0",
                                    "object" => "price",
                                    "active" => true,
                                    "billing_scheme" => "per_unit",
                                    "created" => 1680706423,
                                    "currency" => "usd",
                                    "custom_unit_amount" => null,
                                    "livemode" => false,
                                    "lookup_key" => null,
                                    "metadata" => [
                                    ],
                                    "nickname" => null,
                                    "product" => "prod_NerRxl8GThmNbr",
                                    "recurring" => [
                                        "aggregate_usage" => null,
                                        "interval" => "month",
                                        "interval_count" => 1,
                                        "trial_period_days" => null,
                                        "usage_type" => "licensed"
                                    ],
                                    "tax_behavior" => "unspecified",
                                    "tiers_mode" => null,
                                    "transform_quantity" => null,
                                    "type" => "recurring",
                                    "unit_amount" => 25000,
                                    "unit_amount_decimal" => "25000"
                                ],
                                "quantity" => 1,
                                "subscription" => "sub_1N7kpCAyqeO9IwLwSOoUyZaO",
                                "tax_rates" => [
                                ]
                            ]
                        ],
                        "has_more" => false,
                        "total_count" => 1,
                        "url" => "/v1/subscription_items?subscription=sub_1N7kpCAyqeO9IwLwSOoUyZaO"
                    ],
                    "latest_invoice" => "in_1N7krQAyqeO9IwLw6mVHGl9g",
                    "livemode" => false,
                    "metadata" => [
                        "name" => "default"
                    ],
                    "next_pending_invoice_item_invoice" => null,
                    "on_behalf_of" => null,
                    "pause_collection" => null,
                    "payment_settings" => [
                        "payment_method_options" => null,
                        "payment_method_types" => null,
                        "save_default_payment_method" => "off"
                    ],
                    "pending_invoice_item_interval" => null,
                    "pending_setup_intent" => "seti_1N7kpFAyqeO9IwLw5Ch6sxqI",
                    "pending_update" => null,
                    "plan" => [
                        "id" => "price_1MtXiVAyqeO9IwLwExBohum0",
                        "object" => "plan",
                        "active" => true,
                        "aggregate_usage" => null,
                        "amount" => 25000,
                        "amount_decimal" => "25000",
                        "billing_scheme" => "per_unit",
                        "created" => 1680706423,
                        "currency" => "usd",
                        "interval" => "month",
                        "interval_count" => 1,
                        "livemode" => false,
                        "metadata" => [
                        ],
                        "nickname" => null,
                        "product" => "prod_NerRxl8GThmNbr",
                        "tiers_mode" => null,
                        "transform_usage" => null,
                        "trial_period_days" => null,
                        "usage_type" => "licensed"
                    ],
                    "quantity" => 1,
                    "schedule" => null,
                    "start_date" => 1684093402,
                    "status" => "active",
                    "test_clock" => null,
                    "transfer_data" => null,
                    "trial_end" => null,
                    "trial_settings" => [
                        "end_behavior" => [
                            "missing_payment_method" => "create_invoice"
                        ]
                    ],
                    "trial_start" => null
                ],
                "previous_attributes" => [
                    "cancel_at" => null,
                    "cancel_at_period_end" => false,
                    "canceled_at" => null,
                    "cancellation_details" => [
                        "reason" => null
                    ]
                ]
            ],
            "livemode" => false,
            "pending_webhooks" => 2,
            "request" => [
                "id" => "req_NNfJwRPaeRO61m",
                "idempotency_key" => "becd025e-52cb-42ea-981d-dc7dbc073afa"
            ],
            "type" => "customer.subscription.updated"
        ], $attributes);
    }
}
