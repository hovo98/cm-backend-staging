<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class BrokerLender
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class BrokerLender extends Pivot
{
    protected $table = 'broker_lender';
}
