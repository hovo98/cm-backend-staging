<?php

namespace App\GraphQL\Mutations\Quote;

use App\ForbiddenMessages;
use App\Jobs\JobQuoteSendErrorMessage;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Cache;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class QuoteErrorMessage
{
    /**
     * Return a value for the field.
     *
     * @param  null  $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param  mixed[]  $args The arguments that were passed into the field.
     * @param  \Nuwave\Lighthouse\Support\Contracts\GraphQLContext  $context Arbitrary data that is shared between all fields of a single query.
     * @param  \GraphQL\Type\Definition\ResolveInfo  $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = $context->user();
        $quoteID = $args['quote_id'];
        $dealID = $args['deal_id'];
        $message = $args['message'];

        $ForbiddenMessages = ForbiddenMessages::create([
            'user_id' => $user->id,
            'quote_id' => $quoteID,
            'message' => $message,
        ]);

        $this->sendErrorEmail($user, $quoteID, $dealID);

        return [
            'status' => true,
        ];
    }

    protected function sendErrorEmail(User $user, $quoteID, $dealID): void
    {
        $cacheKey = 'error-status-quote'.$quoteID.'-'.$dealID;

        if (! Cache::get($cacheKey)) {
            Cache::put($cacheKey, 1);
            JobQuoteSendErrorMessage::dispatch($user, $quoteID, $dealID)->delay(now()->addMinutes(5));
        }
    }
}
