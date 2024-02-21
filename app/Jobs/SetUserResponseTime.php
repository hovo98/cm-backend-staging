<?php

namespace App\Jobs;

use App\Message;
use App\User;
use Carbon\CarbonInterval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SetUserResponseTime implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Collection $users)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->users->each(function (User $user) {
            $responseTimes = Message::query()
                ->selectRaw('EXTRACT(EPOCH FROM (updated_at - created_at)) AS difference')
                ->where('seen', true)
                ->whereNull('deleted_at')
                ->where('user_id', '=', $user->id)
                ->pluck('difference')
                ->avg();

            $responseTime = CarbonInterval::seconds($responseTimes)->cascade()->totalMinutes;

            $user->update(['chat_response_time' => round($responseTime, 2)]);
        });
    }
}
