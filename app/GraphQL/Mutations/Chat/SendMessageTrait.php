<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Chat;

use App\Jobs\JobChatSendErrorMessage;
use App\Jobs\JobChatSendMessage;
use App\Message;
use App\Services\RealTime\RealTimeServiceInterface;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Class SendMessageTrait Controller
 *
 * @author Nikola Popov
 */
trait SendMessageTrait
{
    protected function getTimezone(object $createdAt, string $timezone): object
    {
        $dt = Carbon::parse($createdAt)->timezone($timezone);
        $toDay = $dt->format('d');
        $toMonth = $dt->format('m');
        $toYear = $dt->format('Y');
        $dateUTC = Carbon::createFromDate($toYear, $toMonth, $toDay, 'UTC');
        $datePST = Carbon::createFromDate($toYear, $toMonth, $toDay, $timezone);
        $difference = $dateUTC->diffInHours($datePST);
        $date = $dt->addHours($difference);

        return $date;
    }

    protected function setupPusher()
    {
        return app(RealTimeServiceInterface::class)->makeOne();
    }

    protected function saveMessages($roomId, int $id, string $msg, $forbidden = false): Message
    {
        $newMessage = new Message();
        $newMessage->room_id = $roomId;
        $newMessage->user_id = $id;
        $newMessage->message = $msg;
        $newMessage->forbidden_msg = $forbidden;
        if ($forbidden) {
            $newMessage->seen = true;
        }
        $newMessage->save();

        return $newMessage;
    }

    protected function sendEmail(User $user, $room_id, int $deal_id, int $room, string $companyName = ''): void
    {
        $cacheKey = 'status-'.$deal_id.'-'.$room_id.'-'.$user->id;

        if (! Cache::get($cacheKey)) {
            Cache::put($cacheKey, 1);
            JobChatSendMessage::dispatch($user, $room_id, $deal_id, $room, $companyName)->delay(now()->addMinutes(5));
        }
    }

    protected function sendErrorEmail(User $user, $room_id, int $deal_id, int $room, $userOr): void
    {
        $cacheKey = 'error-status-'.$deal_id.'-'.$room_id.'-'.$user->id;

        if (! Cache::get($cacheKey)) {
            Cache::put($cacheKey, 1);
            JobChatSendErrorMessage::dispatch($user, $room_id, $deal_id, $room, 'msg', $userOr)->delay(now()->addMinutes(5));
        }
    }
}
