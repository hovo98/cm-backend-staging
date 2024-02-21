<?php

namespace App\Listeners;

use App\User;
use Illuminate\Mail\Events\MessageSending;

/**
 * Class CheckAllowedEmails
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class CheckAllowedEmails
{
    public function handle(MessageSending $event)
    {
        //Check if not set mailable. Settled in the mail specific class.
        if (! isset($event->message->mailable)) {
            return true;
        }
        if ($event->message->mailable) {
            return true;
        }

        // Get mail to
        $mailToUsers = $event->message->getTo();
        foreach ($mailToUsers as $key => $mailToUser) {
            //Check if it should be sent
            $allowedUsers = User::allowedUsersBeta(trim($key));
            if (! $allowedUsers) {
                return false;
            }
        }
        // Else send mail
        return true;
    }
}
