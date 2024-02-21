<?php

declare(strict_types=1);

namespace App\Jobs;

use App\ForbiddenMessages;
use App\Mail\ErrorEmail;
use App\Mail\SendQuoteErrorMessage;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class JobQuoteSendErrorMessage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $user;

    protected $quote_id;

    protected $deal_id;

    protected $cacheKey;

    public function __construct($user, $quote_id, $deal_id)
    {
        $this->user = $user;
        $this->quote_id = $quote_id;
        $this->deal_id = $deal_id;
        $this->cacheKey = 'error-status-quote'.$this->quote_id.'-'.$this->deal_id;
    }

    public function handle()
    {
        $count = $this->countMsg();
        Log::info('count quote');
        Log::info($count);

        Cache::forget($this->cacheKey);
        if ($count > 0) {
            try {
                Mail::send(new SendQuoteErrorMessage($this->user, $this->quote_id, $this->deal_id));
            } catch (\Throwable $exception) {
                Mail::send(new ErrorEmail($this->user->email, 'Send error quote message email', $exception));
            }
        }
    }

    public function countMsg()
    {
        $newDateTime = Carbon::now()->subMinutes(6);

        $count = 0;
        $msgs = ForbiddenMessages::select()->where('user_id', $this->user->id)->where('quote_id', $this->quote_id)->where('created_at', '>=', $newDateTime)->get();

        if (count($msgs) > 0) {
            return count($msgs);
        }

        return $count;
    }

    public function failed(Throwable $exception)
    {
        Cache::forget($this->cacheKey);
        Mail::send(new ErrorEmail($this->user->email, 'Send error quote message email', $exception));
    }
}
