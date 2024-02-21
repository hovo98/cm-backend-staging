<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Chat;

use App\Deal;
use App\Enums\DealPurchaseType;
use App\Exceptions\PaymentException;
use App\Notifications\LenderInitiatedVideoChat;
use App\Services\VideoCall\VideoCallInterface;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\App;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class GetChatVideoCall
{
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
        $videoCall = App::make(VideoCallInterface::class);

        /** @var User $user */
        $user = $context->user();

        $input = collect($args)->toArray();
        $deal = Deal::find($input['deal_id']);

        if ($deal->getDealType() !== DealPurchaseType::PURCHASED_VIA_SUBSCRIPTION->dealType()) {

            if ($user->isLender()) {
                $deal->broker->notify(new LenderInitiatedVideoChat($deal));
            }

            throw new PaymentException("premium_deal_required");
        }

        $videoCall->createRoomUrl($this->buildAgenda($deal));

        return [
            'start_url' => $videoCall->getStartUrl(),
            'join_url' => $videoCall->getJoinUrl(),
        ];
    }

    private function buildAgenda(?Deal $deal = null): string
    {
        if (is_null($deal)) {
            return 'Finance Lobby';
        }

        $street = $deal?->data['location']['street_address'] ?? '';
        $street2 = $deal?->data['location']['street_address_2'] ?? '';
        $address = trim($street . ' ' . $street2);

        return trim('Finance Lobby ' . $address);
    }
}
