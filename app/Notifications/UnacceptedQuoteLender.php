<?php

namespace App\Notifications;

use App\Deal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

/**
 * Class UnacceptedQuoteLender
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class UnacceptedQuoteLender extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The callback that should be used to build the mail message.
     *
     * @var \Closure|null
     */
    public static $toMailCallback;

    /** @var Deal */
    protected $deal;

    public function __construct($dealId)
    {
        $this->queue = 'emails';
        $this->deal = Deal::query()->find($dealId);
    }

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->view('mail.unacceptedQuoteLender', [
                'lenderName' => $notifiable->first_name,
                'dealsUrl' => config('app.frontend_url').'/lender-deals',
                'year' => date('Y'),
            ])
            ->subject(Lang::get('Your quote for the :loanType in :city was declined', [
                'loanType' => $this->getLoanType(),
                'city' => $this->getCity(),
            ]));
    }

    private function getCity(): string
    {
        $location = $this->deal->data['location'];

        return $location['city'];
    }

    private function getLoanType(): string
    {
        $loanType = $this->deal->data['inducted']['loan_type'];
        $loanTypes = collect(Deal::LOAN_TYPE);

        return Str::lower($loanTypes->get($loanType));
    }

    /**
     * Set a callback that should be used when building the notification mail message.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function toMailUsing($callback)
    {
        static::$toMailCallback = $callback;
    }
}
