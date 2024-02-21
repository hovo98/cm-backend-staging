<?php

namespace App\Listeners;

use App\Events\QuoteRejected;
use App\Mail\ErrorEmail;
use App\Notifications\PasswordResetNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendPasswordResetNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  QuoteRejected  $event
     * @return void
     */
    public function handle(PasswordReset $event)
    {
        $userToBeNotified = $event->user;

        try {
            $userToBeNotified->notify(new PasswordResetNotification());
        } catch (\Throwable $exception) {
            Mail::send(new ErrorEmail($userToBeNotified->email, 'Send email to user about the password reset being successful', $exception));
        }
    }
}
