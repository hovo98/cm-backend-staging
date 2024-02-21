<?php

namespace App\Http\Controllers;

use App\Message;
use App\Room;

class MessagesController extends Controller
{
    public function messages()
    {
        //Paginate messages by 10
        $limit = 10;

        //Get rooms
        $rooms = Room::paginate($limit);

        return view('pages.messages', [
            'rooms' => $rooms,
        ]);
    }

    public function threads($room_id)
    {
        $room = Room::find($room_id);
        $messages = Message::where('room_id', $room_id)->get();

        return view('pages.threads', [
            'messages' => $messages,
            'room' => $room,
        ]);
    }
}
