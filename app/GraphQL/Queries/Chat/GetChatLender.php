<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Chat;

use App\Deal as DealEloquent;
use App\GraphQL\Mutations\Chat\SendMessageTrait;
use App\Message;
use App\Room;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class GetChatLender
 *
 * @author Nikola Popov
 */
class GetChatLender
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
        $user = $context->user();
        $input = collect($args)->toArray();

        // find broker by deal id
        $dealEloquent = DealEloquent::find($input['deal_id']);
        $dealOwner = $dealEloquent->user_id;

        // find specific room
        $findRoom = $dealOwner.$input['deal_id'].$user->id;
        $room = Room::firstWhere('room', intval($findRoom));

        if ($room) {
            // map data
            $getMessages = Message::where('room_id', $room->id)->orderBy('created_at', 'asc')->get();
            $messages = $this->mapData($getMessages, $user);

            return [
                'name' => '',
                'chat_response_time' => $room->broker->chat_response_time,
                'room_id' => intval($findRoom),
                'messages' => $messages,
            ];
        }

        return null;
    }

    private function mapData($messages, $user): array
    {
        $arr = [];
        foreach ($messages as $obj) {
            if ($user->id !== $obj->user_id && $obj->forbidden_msg) {
                continue;
            }

            $role = ($user->id === $obj->user_id) ? 'lender' : 'broker';

            $time = $this->getTimezone($obj->created_at, $user->timezone)->toDateTimeString();

            $arr[] = [
                'id' => $obj->id,
                'role' => $role,
                'time' => $time,
                'message' => $obj->message,
                'seen' => $obj->seen,
                'forbidden_msg' => $obj->forbidden_msg,
            ];
        }

        return $arr;
    }
}
