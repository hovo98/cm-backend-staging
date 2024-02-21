<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Quote;

use App\Services\MapperServices\Lender\Quote\ActiveQuote as MapperService;
use App\Services\QueryServices\Lender\Quotes\ActiveQuote as QueryService;
use App\Services\TypeServices\Lender\Quotes\ActiveQuote as TypeService;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class ActiveQuote
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class ActiveQuote
{
    /**
     * @param    $rootValue
     * @param  array  $args
     * @param  GraphQLContext|null  $context
     * @param  ResolveInfo  $resolveInfo
     * @return array
     *
     * @throws \Exception
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $user = $context->user();
        //Check if logged user is as from email
        $payload = decrypt($args['token']);
        $quote_id = $payload['quote_id'];
        $deal_id = $payload['deal_id'];
        $is_active = $payload['is_active'];

        $typeService = new TypeService($user, $quote_id, $deal_id, $is_active);

        return $typeService->fmap(new QueryService(), new MapperService());
    }
}
