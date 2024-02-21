<?php

declare(strict_types=1);

namespace App\Jobs\Broker\Quote;

use App\Deal;
use App\Mail\ErrorEmail;
use App\Notifications\ChooseQuoteBroker as ChooseQuoteBrokerNotification;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

/**
 * Class ChooseQuoteBroker
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class ChooseQuoteBroker
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $deals;

    public function __construct()
    {
        // Get deals that accepted second quote
        $deals = Deal::where('finished', true)->whereNull('deleted_at')
                    ->whereNotNull('second_quote_accepted_at')
                    ->where('second_quote_accepted_at', '<', Carbon::now()->subWeek(2))
                    ->whereNull('second_quote_notify');
        $this->deals = $deals->get();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->deals as $deal) {
            $user = $deal->broker()->first();
            $deal->second_quote_notify = 1;
            $deal->save();
            try {
                $user->notify(new ChooseQuoteBrokerNotification($deal->id));
            } catch (\Throwable $exception) {
                Mail::send(new ErrorEmail($user->email, 'Send email to broker to choose which quote they went with after 2 quotes were accepted', $exception));
            }
        }
    }
}
