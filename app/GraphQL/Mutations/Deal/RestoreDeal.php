<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Deal;

use App\Deal;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class RestoreDeal
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class RestoreDeal
{
    /**
     * @param $rootValue
     * @param  array  $args
     * @param  GraphQLContext|null  $context
     * @param  ResolveInfo  $resolveInfo
     * @return array
     *
     * @throws \Exception
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        // Put args into array
        $input = collect($args)->toArray();

        // Get Deal id
        $deal_id = $input['id'];

        // Find Deal in trashed deals
        $deal = Deal::withTrashed()->find($deal_id);

        // If there is no Deal
        if (! $deal) {
            return [
                'success' => false,
                'message' => 'The deal is not in trash.',
            ];
        }

        // If Deal exists restore it from trash
        $deal->restore();

        return [
            'success' => true,
            'message' => 'The deal will be restored.',
        ];
    }
}
