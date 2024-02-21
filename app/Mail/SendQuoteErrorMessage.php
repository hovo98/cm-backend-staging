<?php

namespace App\Mail;

use App\DataTransferObjects\DealMapper;
use App\ForbiddenMessages;
use App\Message;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SendQuoteErrorMessage extends Mailable
{
    use Queueable;
    use SerializesModels;

    private $user;

    private $quote_id;

    private $deal_id;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $quote_id, $deal_id)
    {
        $this->user = $user;
        $this->quote_id = $quote_id;
        $this->deal_id = $deal_id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        Log::info('last quote');

        $mapper = new DealMapper($this->deal_id);
        $deal = $mapper->mapFromEloquent();
        $location = $deal['location']['city'].', '.$deal['location']['state'];

        $longMessages = $this->getLastMessages();
        $subject = 'Alert: '.$this->user->first_name.' '.$this->user->last_name.' attempted to send contact information';

        $admins = User::where('role', '=', 'admin')->pluck('email')->toArray();

        return $this->from('no-reply@financelobby.com', 'Finance Lobby')
                    ->to($admins)
                    ->subject($subject)
                    ->view('mail.newChatErrorMessage', [
                        'first_name' => $this->user->first_name,
                        'last_name' => $this->user->last_name,
                        'email' => $this->user->email,
                        'lastMessages' => $longMessages,
                        'location' => $location,
                        'from' => 'a quote',
                        'year' => date('Y'),
                    ]);
    }

    private function getLastMessages()
    {
        $newDateTime = Carbon::now()->subMinutes(6);

        $msgs = ForbiddenMessages::select()->where('user_id', $this->user->id)->where('quote_id', $this->quote_id)->where('created_at', '>=', $newDateTime)->get();

        if (! $msgs) {
            return [];
        }

        return $msgs;
    }
}
