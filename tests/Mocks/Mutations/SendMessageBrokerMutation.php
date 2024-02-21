<?php

namespace Tests\Mocks\Mutations;

use App\Deal;
use App\Lender;
use App\Room;

class SendMessageBrokerMutation
{
    public Deal $deal;
    public Lender $lender;
    public Room $room;
    public string $message;

    public function __construct(Deal $deal, Lender $lender, Room $room, string $message = 'Say something nice.')
    {
        $this->deal = $deal;
        $this->lender = $lender;
        $this->room = $room;
        $this->message = $message;
    }

    public function __toString()
    {
        return '
                mutation {
                    chatSendMessageBroker (input: {msg: "' . $this->message . '", room_id: ' . $this->room->room . ', deal_id: ' . $this->deal->id . ', forbidden_msg: false, lender_id: 0}) {
                        room_id
                        chat {
                            id
                            role
                            time
                            message
                            seen
                            forbidden_msg
                        }
                    }
                }
            ';
    }
}
