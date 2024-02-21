<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Chat;

use App\Deal as DealEloquent;
use App\Quote;
use App\Room;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class SendMessageLender
 *
 * @author Nikola Popov
 */
class SendMessageLender
{
    use SendMessageTrait;

    /**
     * Return a value for the field.
     *
     * @param  null  $rootValue  Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param  mixed[]  $args  The arguments that were passed into the field.
     * @param  GraphQLContext  $context  Arbitrary data that is shared between all fields of a single query.
     * @param  ResolveInfo  $resolveInfo  Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return array
     */
    public function resolve($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        // Get currently logged in user
        $user = $context->user();
        $input = collect($args)->toArray();

        $room = $this->createOrGetRoom($input['deal_id'], $user->id, (int) $input['room_id']);
        $newMessage = $this->saveMessages($room->id, $user->id, $input['msg'], $input['forbidden_msg']);

        $pusher = $this->setupPusher();
        // get broker for timezone
        $broker = User::find($room->broker_id);

        $time = $this->getTimezone($newMessage->created_at, $broker->timezone);

        $dataToReturn = [
            'deal_id' => $room->deal_id,
            'room_id' => $room->room,
            'chat' => [
                'id' => $newMessage->id,
                'role' => $user->role,
                'time' => $time->toDateTimeString(),
                'message' => $input['msg'],
                'seen' => false,
                'forbidden_msg' => $input['forbidden_msg'],
            ],
        ];

        if (! $input['forbidden_msg']) {
            $channel = strval(config('app.pusher_app_channel')).strval($room->broker_id);

            if ($input['room_id'] === 0) {
                $name = $this->getNameForChat($room);
                $messages = [];
                $messages[] = [
                    'id' => $newMessage->id,
                    'role' => $user->role,
                    'time' => $time,
                    'message' => $input['msg'],
                    'seen' => false,
                    'forbidden_msg' => $input['forbidden_msg'],
                ];
                $pusher->trigger($channel, 'FirstMessageSent', [
                    'deal_id' => $room->deal_id,
                    'name' => $name,
                    'room_id' => $room->room,
                    'messages' => $messages,
                    'forbidden_msg' => $input['forbidden_msg'],
                ]);
            } else {
                $pusher->trigger($channel, 'MessageSent', $dataToReturn);
            }

            $this->sendEmail($broker, $room->id, $room->deal_id, $room->room);
        } else {
            $this->sendErrorEmail($broker, $room->id, $room->deal_id, $room->room, $user);
        }

        $dataToReturn['chat']['time'] = $this->getTimezone($newMessage->created_at, $user->timezone)->toDateTimeString();
        unset($dataToReturn['deal_id']);

        return $dataToReturn;
    }

    private function saveRoom(int $dealOwner, int $userId, int $deal_id): Room
    {
        $newRoom = new Room();
        $newRoomId = $dealOwner.$deal_id.$userId;

        $newRoom->broker_id = $dealOwner;
        $newRoom->lender_id = $userId;
        $newRoom->deal_id = $deal_id;
        $newRoom->company = 'company';
        $newRoom->room = (int) $newRoomId;
        $newRoom->save();

        return $newRoom;
    }

    private function createOrGetRoom(int $deal_id, int $userId, $room_id): Room
    {
        $room = Room::where('deal_id', $deal_id)
            ->where('lender_id', $userId)
            ->first();

        if (!$room) {
            $dealEloquent = DealEloquent::find($deal_id);
            $dealOwner = $dealEloquent->user_id;
            $room = $this->saveRoom((int) $dealOwner, $userId, $deal_id);
        }

        return $room;
    }

    private function getNameForChat($obj): string
    {
        $name = 'Messages with Lender';
        $dealCountQuotes = Quote::where('user_id', $obj->lender_id)->where('deal_id', $obj->deal_id)->where('finished', true)->count();
        if ($dealCountQuotes > 0) {
            $quoteStatusCheck = Quote::where('user_id', $obj->lender_id)->where('deal_id', $obj->deal_id)->where('status', 2)->count();
            $lenderUser = User::find($obj->lender_id);
            if ($quoteStatusCheck > 0) {
                $name = 'Messages with '.$lenderUser->first_name.' '.$lenderUser->last_name;
            } else {
                $companyName = $lenderUser->getCompanyNameFromMetasOrFromCompanyRelationship();
                $name = 'Messages with '.$companyName;
            }
        }

        return $name;
    }
}
