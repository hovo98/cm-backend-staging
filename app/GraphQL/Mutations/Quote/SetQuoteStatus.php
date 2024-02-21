<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Quote;

use App\Exceptions\PaymentException;
use App\Services\MapperServices\Broker\Quotes\SetQuoteStatus as MapperService;
use App\Services\QueryServices\Broker\Quotes\SetQuoteStatus as QueryService;
use App\Services\TypeServices\Broker\Quotes\SetQuoteStatus as TypeService;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class SetQuoteStatus
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class SetQuoteStatus
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
        $quote = \App\Quote::find($args['id']);

        $status = $args['status'];

        throw_if(
            $status === \App\Quote::ACCEPTED && !$user->canAccept($quote->deal),
            new PaymentException("subscription_upgrade_required")
        );

        if (isset($args['unacceptMessage'])) {
            $typeService = new TypeService($user, $args['id'], $args['status'], $args['unacceptMessage']);
        } else {
            $typeService = new TypeService($user, $args['id'], $args['status']);
        }

        return $typeService->fmap(new QueryService(), new MapperService());
    }
}
