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
class LenderAllQuotes
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
        $quotesQuery = Quote::where('user_id', $user->id)->groupBy('quotes.deal_id', 'quotes.id');

        $perPage = isset($args['perPage']) ? $args['perPage'] : false;
        $page = isset($args['page']) ? $args['page'] : false;

        return QuoteMapper::mapQuery($quotesQuery, $perPage, $page);
    }

    protected function mappedLender($lender)
    {
        return [
            'id' => $lender->id,
            'firstName' => $lender->first_name,
            'lastName' => $lender->last_name,
        ];
    }

    protected function mappedQuotes($quotes)
    {
        $mappedQuotes = [];
        foreach ($quotes as $quote) {
            $quoteMapper = new QuoteMapper($quote->id);
            $mappedQuote = $quoteMapper->mapFromEloquent();
            $mappedQuotes[] = $mappedQuote;
        }

        return $mappedQuotes;
    }
}
