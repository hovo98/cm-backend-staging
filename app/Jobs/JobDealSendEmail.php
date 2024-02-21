<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Events\DealChanged;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class DealCreated
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class JobDealSendEmail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $deal;

    public function __construct($deal)
    {
        $this->deal = $deal;
    }

    public function handle()
    {
        event(new DealChanged($this->deal, 'createdPublished'));
    }
}
