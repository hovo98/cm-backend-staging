<?php

declare(strict_types=1);

namespace App\Jobs\Admin;

use App\Mail\ErrorEmail;
use App\Notifications\SuspiciousLocationsAdmin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Class SuspiciousLocations
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class SuspiciousLocations implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $admin;

    public $name;

    public $company;

    public function __construct($admin, $name, $company)
    {
        $this->admin = $admin;
        $this->name = $name;
        $this->company = $company;
    }

    public function handle()
    {
        try {
            $this->admin->notify(new SuspiciousLocationsAdmin($this->name, $this->company));
        } catch (\Throwable $exception) {
            Mail::send(new ErrorEmail($this->admin->email, 'Send admin suspicious locations', $exception));
        }
    }
}
