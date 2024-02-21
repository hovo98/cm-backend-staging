<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Lender;
use App\User;
use Illuminate\Support\Facades\Log;

/**
 * Class UserEventSubscriber
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class UserEventSubscriber
{
    /**
     * Send email when user is registered
     *
     * @param $event
     */
    public function sendVerificationMail($event)
    {
        /** @var User $user */
        $user = $event->user;
        $user->sendEmailVerificationNotification();
    }

    /**
     * Send email when lender is registered
     *
     * @param $event
     */
    public function sendWelcomeLenderMail($event)
    {
        /** @var Lender $lender */
        $lender = $event->model();

        Log::debug($event->event());

        if ($event->event() === 'createdLender') {
            // Get deal preferences
            $deal_preferences = $lender->getPerfectFit();
            // Call method from Lender
            $lender->sendWelcomeEmailConfirmation($deal_preferences);
            // Check suspicious locations
            $lender->checkSuspiciousLocations($deal_preferences);
        }
    }

    /**
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Illuminate\Auth\Events\Registered',
            'App\Listeners\UserEventSubscriber@sendVerificationMail'
        );
        $events->listen(
            \App\Events\UserChanged::class,
            'App\Listeners\UserEventSubscriber@sendWelcomeLenderMail'
        );
    }
}
