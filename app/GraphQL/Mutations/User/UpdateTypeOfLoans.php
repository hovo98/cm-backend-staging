<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\User;

use App\DataTransferObjects\Fit;
use App\Lender;
use GraphQL\Error\Error;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class UpdateTypeOfLoans
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class UpdateTypeOfLoans
{
    /**
     * @param $rootValue
     * @param  array  $args
     * @param  GraphQLContext|null  $context
     * @param  ResolveInfo  $resolveInfo
     * @return array
     *
     * @throws Error
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        if ($context->user()->role !== 'lender') {
            throw new Error('Only Lenders have the Perfect Fit and Close Fit.');
        }

        $lender = Lender::find($context->user()->id);
        $preferences = $lender->getPerfectFit();
        if (! $preferences) {
            return [
                'success' => false,
                'message' => 'Missing other required preferences.',
            ];
        }
        $typeOfLoans = $args['type_of_loans'];

        $fit = new Fit($preferences->getAreas(), $preferences->getLoanSize(), $preferences->getAssetTypes(), $preferences->getMultifamily() ?? [], $preferences->getOtherAssetTypes() ?? [], $typeOfLoans);

        $updated = $lender->updateFit('perfect', $fit);
        if (! $updated) {
            throw new Error('An error occured, please try again');
        }

        return [
            'success' => true,
            'message' => 'Your profile has been updated',
        ];
    }
}
