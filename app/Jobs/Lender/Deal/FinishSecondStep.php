<?php

declare(strict_types=1);

namespace App\Jobs\Lender\Deal;

use App\Mail\ErrorEmail;
use App\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

/**
 * Class FinishSecondStep
 */
class FinishSecondStep
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $users;

    public function __construct()
    {
        // Get Lenders that didn't complete second step and weren't notified
        $preparedQuery = $this->checkEnvironment();
        $users = $preparedQuery->get();
        $this->users = $users;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Send notification for each user and flag that notification is sent
        foreach ($this->users as $user) {
            $user->notify = true;
            $user->save();
            try {
                $user->notify(new \App\Notifications\FinishSecondStep());
            } catch (\Throwable $exception) {
                Mail::send(new ErrorEmail($user->email, 'Lenders that didnt complete second step and werent notified', $exception));
            }
        }
    }

    /**
     * @return mixed
     */
    private function checkEnvironment()
    {
        $query = User::where('role', 'lender')
            ->where('created_at', '<', Carbon::now()->subHour())
            ->whereNull('notify')
            ->whereNull('metas');

        $envCheck = config('app.app_check_env');
        if ($envCheck === 'beta') {
            $query->where('site', User::BETA_SIGNUP);
        } elseif ($envCheck === 'live') {
            $query->where('site', User::LIVE_SIGNUP);
        } elseif ($envCheck === 'premvpstage') {
            $query->where('site', User::LIVE_SIGNUP);
        } else {
            $query->where('site', User::BETA_SIGNUP);
        }

        return $query;
    }
}
