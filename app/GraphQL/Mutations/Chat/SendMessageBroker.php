<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Chat;

use App\Room;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class SendMessageBroker
 *
 * @author Nikola Popov
 */
class SendMessageBroker
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

        $room = $this->createOrGetRoom($input['deal_id'], $input['lender_id'], (int) $input['room_id'], $user->id);
        $role = $user->role;

        //$room = Room::firstWhere('room', $input['room_id']);
        $newMessage = $this->saveMessages($room->id, $user->id, $input['msg'], $input['forbidden_msg']);

        $pusher = $this->setupPusher();
        // get lender for timezone
        $lender = User::find($room->lender_id);

        $time = $this->getTimezone($newMessage->created_at, $lender->timezone);

        $dataToReturn = [
            'deal_id' => $room->deal_id,
            'room_id' => $room->room,
            'chat' => [
                'id' => $newMessage->id,
                'role' => $role,
                'time' => $time->toDateTimeString(),
                'message' => $input['msg'],
                'seen' => false,
                'forbidden_msg' => $input['forbidden_msg'],
            ],
        ];

        if (! $input['forbidden_msg']) {
            $channel = strval(config('app.pusher_app_channel')).strval($room->lender_id);
            $pusher->trigger($channel, 'MessageSent', $dataToReturn);
        }

        $time = $this->getTimezone($newMessage->created_at, $user->timezone);

        $dataToReturn['chat']['time'] = $time->toDateTimeString();
        unset($dataToReturn['deal_id']);

        $companyName = $user->getCompanyNameFromMetasOrFromCompanyRelationship();

        if (! $input['forbidden_msg']) {
            $this->sendEmail($lender, $room->id, $room->deal_id, $room->room, $companyName);
        } else {
            $this->sendErrorEmail($lender, $room->id, $room->deal_id, $room->room, $user);
        }

        return $dataToReturn;
    }

    private function createOrGetRoom(int $deal_id, int $lenderId, $room_id, $userId): Room
    {
        if ($room_id && is_int($room_id) && Room::where('room', $room_id)->exists()) {
            return Room::where('room', $room_id)->first();
        }
        $room = Room::where('deal_id', $deal_id)
            ->where('lender_id', $lenderId)
            ->where('broker_id', $userId)
            ->first();

        if ($room) {
            return $room;
        }

        return $this->saveRoom($userId, $lenderId, $deal_id);
    }

    private function saveRoom(int $dealOwner, int $lenderId, int $deal_id): Room
    {
        $newRoom = new Room();
        $newRoomId = $dealOwner.$deal_id.$lenderId;

        $newRoom->broker_id = $dealOwner;
        $newRoom->lender_id = $lenderId;
        $newRoom->deal_id = $deal_id;
        $newRoom->company = 'company';
        $newRoom->room = (int) $newRoomId;
        $newRoom->save();

        return $newRoom;
    }
}
