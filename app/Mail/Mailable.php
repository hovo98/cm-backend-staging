<?php

namespace App\Mail;

use Illuminate\Mail\Mailable as BaseMailable; // Extend off laravel mailable

abstract class Mailable extends BaseMailable
{
    /**
     * @param  \Illuminate\Contracts\Mail\Factory|\Illuminate\Contracts\Mail\Mailer  $mailer
     */
    public function send($mailer)
    {
        //Initializes properties on the Swift Message object
        $this->withSymfonyMessage(function ($message) {
            $checkInstanceClass = $this->checkInstanceClass();
            $message->mailable = $checkInstanceClass;
        });

        parent::send($mailer);
    }

    private function checkInstanceClass(): bool
    {
        if ($this instanceof ContactForm || $this instanceof InvitationEmailBrokerToBroker
            || $this instanceof InvitationEmailBrokerToLender
            || $this instanceof InvitationEmailLenderToBroker
            || $this instanceof InvitationEmailLenderToLender) {
            return true;
        }

        return false;
    }
}
