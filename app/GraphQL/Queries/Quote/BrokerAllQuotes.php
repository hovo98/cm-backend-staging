<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Quote;

use App\DataTransferObjects\QuoteMapper;
use App\Quote;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class AllQuotes
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class BrokerAllQuotes
{
    /**
     * @param $rootValue
     * @param  array  $args
     * @param  GraphQLContext|null  $context
     * @param  ResolveInfo  $resolveInfo
     * @return array
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $user = $context->user();
        $filterQuotesQuery = Quote::whereIn('deal_id', function ($query) use ($user) {
            $query->from('deals');
            $query->select('id');
            $query->where('user_id', $user->id);
            $query->where('finished', true);
        })->where('finished', true);

        $perPage = isset($args['perPage']) ? $args['perPage'] : false;
        $page = isset($args['page']) ? $args['page'] : false;

        return QuoteMapper::mapQuery($filterQuotesQuery, $perPage, $page);
    }
}
