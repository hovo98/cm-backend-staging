<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\User;

use App\Broker;
use App\Deal;
use Error;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class ProfileInfoBroker
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class ProfileInfoBroker
{
    /**
     * Return a value for the field.
     *
     * @param  null  $rootValue  Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param  mixed[]  $args  The arguments that were passed into the field.
     * @param  GraphQLContext  $context  Arbitrary data that is shared between all fields of a single query.
     * @param  ResolveInfo  $resolveInfo  Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return array
     *
     * @throws Error
     */
    public function resolve($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        if ($context->user()->role !== 'broker') {
            throw new Error('Only broker have this info.');
        }

        $broker = Broker::find($context->user()->id);
        // Count number of published deals per broker
        $dealsPosted = $broker->deals()->where('finished', true)->count();
        // Count number of quotes for published deals per broker
        $quotesReceived = $broker->quotes->count();
        // Count number of deals where quote is accepted
        $pairedDeals = $broker->deals->where('termsheet', '!=', Deal::OPEN)->count();

        return [
            'dealsPosted' => $dealsPosted ?? 0,
            'quotesReceived' => $quotesReceived ?? 0,
            'pairedDeals' => $pairedDeals ?? 0,
        ];
    }
}
