<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\User;

use App\Lender;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class SetProfileInfoLender
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class SetProfileInfoLender
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
        if ($context->user()->role !== 'lender') {
            throw new Error('Only lenders have this info.');
        }

        $lender = Lender::find($context->user()->id);

        $lender->updateProfileInfoLender([
            'biography' => $args['biography'],
            'specialty' => $args['specialty'],
            'linkedin_url' => $args['linkedin_url'],
        ]);

        $info = $lender->getProfileInfoLender();

        return [
            'biography' => $info['biography'] ?? '',
            'specialty' => $info['specialty'] ?? '',
            'linkedin_url' => $info['linkedin_url'] ?? '',
        ];
    }
}
