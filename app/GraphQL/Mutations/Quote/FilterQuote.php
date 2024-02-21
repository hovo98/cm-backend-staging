<?php

namespace App\GraphQL\Mutations\Quote;

use App\DataTransferObjects\QuoteMapper;
use App\Services\Deal\DealBySponsorName;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class FilterQuote
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
        $dealID = $args['deal']['id'] ?? false;
        $sponsors = $args['sponsors'] ?? '';
        $location = $args['location'] ?? '';
        if ($dealID) {
            $filterQuotesQuery = \App\Quote::where('deal_id', $dealID);

            return $this->result($filterQuotesQuery, $args);
        }
        if ($sponsors and $location) {
            $sponsorDeals = $this->dealIDSBySponsors($sponsors);
            $locationDeals = $this->dealsIDSByLocation($location);
            $deals = array_unique(array_intersect($sponsorDeals, $locationDeals));
            $filterQuotesQuery = \App\Quote::whereIn('deal_id', $deals);

            return $this->result($filterQuotesQuery, $args);
        }
        if ($sponsors) {
            $deals = $this->dealIDSBySponsors($sponsors);
            $filterQuotesQuery = \App\Quote::whereIn('deal_id', $deals);

            return $this->result($filterQuotesQuery, $args);
        }
        if ($location) {
            $deals = $this->dealsIDSByLocation($location);
            $filterQuotesQuery = \App\Quote::whereIn('deal_id', $deals);

            return $this->result($filterQuotesQuery, $args);
        }
    }

    protected function result($query, $args)
    {
        $perPage = isset($args['pagination']['perPage']) ? $args['pagination']['perPage'] : false;
        $page = isset($args['pagination']['page']) ? $args['pagination']['page'] : false;

        return QuoteMapper::mapQuery($query, $perPage, $page);
    }

    protected function dealIDSBySponsors($sponsors)
    {
        $deals = [];
        foreach ($sponsors as $sponsor) {
            $deals[] = DealBySponsorName::getList($sponsor);
        }

        return array_unique(array_merge(...$deals));
    }

    protected function dealsIDSByLocation($location)
    {
        $dealIds = [];
        $deals = \App\Deal::where('data->location->place_id', $location)->get();
        foreach ($deals as $deal) {
            $dealIds[] = $deal->id;
        }

        return $dealIds;
    }
}
