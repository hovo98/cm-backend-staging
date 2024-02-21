<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Chat;

use App\Deal;
use App\GraphQL\Mutations\Chat\SendMessageTrait;
use App\Message;
use App\Room;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class GetChatBroker
 *
 * @author Nikola Popov
 */
class GetChatBroker
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
        $rooms = Room::where('broker_id', $user->id)->where('deal_id', $input['deal_id'])->get();

        $mapRoom = $this->mapData($rooms, $user);

        return [
            'rooms' => $mapRoom,
        ];
    }

    private function mapData($rooms, $user): array
    {
        $newRoom = [];

        foreach ($rooms as $obj) {
            $getMessages = Message::where('room_id', $obj->id)->orderBy('created_at', 'asc')->get();
            $messages = [];

            foreach ($getMessages as $obje) {
                if ($user->id !== $obje->user_id && $obje->forbidden_msg) {
                    continue;
                }

                $role = ($user->id === $obje->user_id) ? 'broker' : 'lender';

                $time = $this->getTimezone($obje->created_at, $user->timezone)->toDateTimeString();

                $messages[] = [
                    'id' => $obje->id,
                    'role' => $role,
                    'time' => $time,
                    'message' => $obje->message,
                    'seen' => $obje->seen,
                    'forbidden_msg' => $obje->forbidden_msg,
                ];
            }

            $name = $this->getNameForChat($obj);

            $wrap = [
                'name' => $name,
                'room_id' => $obj->room,
                'chat_response_time' => $obj->lender->chat_response_time,
                'messages' => $messages,
            ];

            $newRoom[] = $wrap;
        }

        return $newRoom;
    }

    private function getNameForChat($obj): string
    {
        $deal = Deal::find($obj->deal_id);
        if (! $deal->isPremium()) {
            return 'Messages with Lender';
        }

        $lenderUser = User::find($obj->lender_id);

        return 'Messages with '.$lenderUser->first_name.' '.$lenderUser->last_name;
    }
}
