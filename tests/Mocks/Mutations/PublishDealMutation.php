<?php

namespace Tests\Mocks\Mutations;

use App\Deal;

class PublishDealMutation
{
    public Deal $deal;

    public function __construct(Deal $deal)
    {
        $this->deal = $deal;
    }

    public function __toString()
    {
        return '
                mutation {
                    deal(
                        input: {
                            id: '. $this->deal->id.',
                            finished: true
                            force: false
                        }
                    ) {
                        id,
                        finished
                    }
                }
            ';
    }
}
