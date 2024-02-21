<?php

declare(strict_types=1);

namespace App;

use App\Traits\CascadeRestore;
use App\Traits\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class BrokerLenderEmail
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class BrokerLenderEmail extends Pivot
{
    use SoftDeletes;
    use CascadeSoftDeletes;
    use CascadeRestore;
}
