<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\ErrorEmail;
use App\Mail\SendChatErrorMessage;
use App\Message;
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

class JobChatSendErrorMessage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $user;

    protected $room_id;

    protected $deal_id;

    protected $room;

    protected $cacheKey;

    protected $flag;

    protected $userOr;

    public function __construct($user, $room_id, $deal_id, $room, $flag, $userOr)
    {
        $this->user = $user;
        $this->room_id = $room_id;
        $this->deal_id = $deal_id;
        $this->room = $room;
        $this->flag = $flag;
        $this->userOr = $userOr;
        $this->cacheKey = 'error-status-'.$this->deal_id.'-'.$this->room_id.'-'.$this->user->id;
    }

    public function handle()
    {
        $count = $this->countMsg();
        Log::info('count chat');
        Log::info($count);

        Cache::forget($this->cacheKey);
        if ($count > 0) {
            try {
                Mail::send(new SendChatErrorMessage($this->user, $this->room_id, $this->deal_id, $this->room, $count, $this->flag, $this->userOr));
            } catch (\Throwable $exception) {
                Mail::send(new ErrorEmail($this->user->email, 'Send error message email', $exception));
            }
        }
    }

    public function countMsg()
    {
        $newDateTime = Carbon::now()->subMinutes(6);

        Log::info('date time chat');
        Log::info($newDateTime);

        Log::info('room id chat');
        Log::info($this->room_id);

        Log::info('room id chat');
        Log::info($this->user->id);

        $count = 0;
        $msgs = Message::select()->where('room_id', $this->room_id)->where('forbidden_msg', true)->where('created_at', '>=', $newDateTime)->whereNotIn('user_id', [$this->user->id])->get();

        if (count($msgs) > 0) {
            return count($msgs);
        }

        return $count;
    }

    public function failed(Throwable $exception)
    {
        Cache::forget($this->cacheKey);
        Mail::send(new ErrorEmail($this->user->email, 'Send message email', $exception));
    }
}
