<?php

namespace App\Mail;

use App\DataTransferObjects\DealMapper;
use App\Message;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class SendChatErrorMessage extends Mailable
{
    use Queueable;
    use SerializesModels;

    private $user;

    private $room_id;

    private $deal_id;

    private $room;

    private $count;

    private $flag;

    private $userOr;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $room_id, $deal_id, $room, $count, $flag, $userOr)
    {
        $this->user = $user;
        $this->room_id = $room_id;
        $this->deal_id = $deal_id;
        $this->room = $room;
        $this->flag = $flag;
        $this->count = $count;
        $this->userOr = $userOr;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        Log::info('last chat');
        $mapper = new DealMapper($this->deal_id);
        $deal = $mapper->mapFromEloquent();
        $location = $deal['location']['city'].', '.$deal['location']['state'];

        $longMessages = $this->getLastMessages();
        $subject = 'Alert: '.$this->userOr->first_name.' '.$this->userOr->last_name.' attempted to send contact information';

        $admins = User::where('role', '=', 'admin')->pluck('email')->toArray();

        return $this->from('no-reply@financelobby.com', 'Finance Lobby')
                    ->to($admins)
                    ->subject($subject)
                    ->view('mail.newChatErrorMessage', [
                        'first_name' => $this->userOr->first_name,
                        'last_name' => $this->userOr->last_name,
                        'email' => $this->userOr->email,
                        'lastMessages' => $longMessages,
                        'location' => $location,
                        'from' => $this->flag === 'msg' ? 'messages' : 'a quote',
                        'year' => date('Y'),
                    ]);
    }

    private function getLastMessages()
    {
        $newDateTime = Carbon::now()->subMinutes(6);

        $msgs =
        Message::select()
        ->where('room_id', $this->room_id)
        ->where('forbidden_msg', true)
        ->where('created_at', '>=', $newDateTime)
        ->whereNotIn('user_id', [$this->user->id])->get();

        if (! $msgs) {
            return [];
        }

        return $msgs;
    }
}
