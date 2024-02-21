<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Quote;

use App\Services\MapperServices\Broker\Quotes\ChooseQuote as MapperService;
use App\Services\QueryServices\Broker\Quotes\ChooseQuote as QueryService;
use App\Services\TypeServices\Broker\Quotes\ChooseQuote as TypeService;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class ChooseQuote
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class ChooseQuote
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
        //Check if logged user is as from email 'id' => $notifiable->id
        $payload = decrypt($args['token']);
        $quote_id = $payload['quote_id'];
        $deal_id = $payload['deal_id'];
        $choose_both = $payload['choose_both'];

        $typeService = new TypeService($user, $quote_id, $deal_id, $choose_both);

        return $typeService->fmap(new QueryService(), new MapperService());
    }
}
