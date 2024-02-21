<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\ErrorEmail;
use App\Message;
use App\Notifications\SendChatMessage as SendChatMessageNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Throwable;

class JobChatSendMessage implements ShouldQueue
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

    protected $companyName;

    public function __construct($user, $room_id, $deal_id, $room, $companyName)
    {
        $this->user = $user;
        $this->room_id = $room_id;
        $this->deal_id = $deal_id;
        $this->room = $room;
        $this->companyName = $companyName;
        $this->cacheKey = 'status-'.$this->deal_id.'-'.$this->room_id.'-'.$this->user->id;
    }

    public function handle()
    {
        $count = $this->seenMsg();

        Cache::forget($this->cacheKey);
        if ($count > 0) {
            try {
                $this->user->notify(new SendChatMessageNotification($this->user, $this->room_id, $this->deal_id, $this->room, $count, $this->companyName));
            } catch (\Throwable $exception) {
                Mail::send(new ErrorEmail($this->user->email, 'Send message email', $exception));
            }
        }
    }

    public function seenMsg()
    {
        $count = 0;
        $msgs = Message::select()->where('room_id', $this->room_id)->where('forbidden_msg', false)->whereNotIn('user_id', [$this->user->id])->where('seen', false)->get();

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
