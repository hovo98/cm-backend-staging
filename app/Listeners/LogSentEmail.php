<?php

namespace App\Listeners;

use App\EmailLog;
use Illuminate\Mail\Events\MessageSent;

class LogSentEmail
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(MessageSent $event)
    {
        $messageId = $event->message->getHeaders()->get('FL-ID')?->getValue();
        if (!$messageId) {
            return;
        }

        $emailLog = EmailLog::byUuid($messageId)->first();
        if ($emailLog) {
            $emailLog->markSent();
        }
    }
}
