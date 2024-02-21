<?php

namespace Tests\Mocks\Queries;

class CheckPaymentStatusQuery
{
    public string $stripeCheckoutId;

    public function __construct(string $stripeCheckoutId = 'cs_test_a1VauZcr0tnFuHpjuRPvgbLl84LO1VU5hYHWLlro7Q3Z8pTn0bv4PTwqYN')
    {
        $this->stripeCheckoutId = $stripeCheckoutId;
    }

    public function __toString()
    {
        return '
                query {
                    checkPaymentStatus (
                        input: {
                            checkout_id: "'. $this->stripeCheckoutId .'"
                        }
                    ) {
                        status
                    }
                }
            ';
    }
}
