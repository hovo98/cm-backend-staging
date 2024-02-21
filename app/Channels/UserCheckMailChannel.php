<?php

namespace App\Channels;

use App\User;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notification;

/**
 * Class UserCheckMailChannel
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class UserCheckMailChannel extends MailChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        $checkNotification = $notifiable->checkNotification($notification);

        if ($checkNotification) {
            // Check if user should receive emails
            $allowedUsers = User::allowedUsersBeta(trim($notifiable->email));
            if ($notifiable instanceof User && ! $allowedUsers) {
                return;
            }
        }
        // Convert to mail and send it
        $message = $notification->toMail($notifiable);
        if (! $message) {
            return;
        }
        parent::send($notifiable, $notification);
    }
}
