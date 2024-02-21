<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\MailboxListHeader;

class ModifyOutGoingEmailHeaders
{
    /**
     * Handle the event.
     *
     * @param  MessageSending  $event
     */
    public function handle(MessageSending $event)
    {
        $event->message->getHeaders()->addTextHeader('X-Mailgun-Tag', config('app.url'));
        $sender = $event->message->getHeaders()->get('from')->toString();


        if ($sender !== 'no-reply@financelobby.com') {
            if ($event->message->getHeaders()->get('reply-to')) {
                $event->message->getHeaders()->remove('reply-to');
            }

            $address = new Address(config('mail.reply_to.address'), config('mail.reply_to.name'));

            $header = new MailboxListHeader('Reply-To', [$address]);

            $event->message->getHeaders()->add($header);
        }
    }
}
