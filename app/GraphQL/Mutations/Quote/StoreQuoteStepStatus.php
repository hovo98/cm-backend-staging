<?php

namespace App\GraphQL\Mutations\Quote;

use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Log;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class StoreQuoteStepStatus
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
        //        $user = User::find(52);

        $quoteID = $args['id'];
        try {
            $mapper = new \App\DataTransferObjects\QuoteMapper($quoteID);
            if ($mapper) {
                $quote = $mapper->storeStatus($args['status']);
                $quote->save();
            }
        } catch (\Exception $e) {
            Log::warning('JsonbMapper cant be created in store deal step status');
            Log::warning($e->getMessage());
        }

        return  $mapper->mapFromEloquent();
    }
}
