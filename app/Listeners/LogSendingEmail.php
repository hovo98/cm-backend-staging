<?php

namespace App\Listeners;

use App\EmailLog;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Str;

class LogSendingEmail
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(MessageSending $event)
    {
        // Create and add a UUID to the header so we can identify the email
        // after it's been sent and mark it sent.
        $messageId = Str::orderedUuid();
        $event->message->getHeaders()->addTextHeader('FL-ID', $messageId);

        EmailLog::makeOne(
            uuid: $messageId,
            recipients: $event->message->getTo(),
            subject: $event->message->getSubject(),
            message: $event->message->getHtmlBody(),
        );
    }
}
