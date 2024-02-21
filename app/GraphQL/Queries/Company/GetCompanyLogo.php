<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\Company;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class GetCompanyLogo
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class GetCompanyLogo
{
    /**
     * Return a value for the field.
     *
     * @param  null  $rootValue  Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param  mixed[]  $args  The arguments that were passed into the field.
     * @param  GraphQLContext  $context  Arbitrary data that is shared between all fields of a single query.
     * @param  ResolveInfo  $resolveInfo  Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return array
     */
    public function resolve($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = $context->user();
        $company = $user->getCompany();
        $companyLogo = $company['company_logo'] ?? '';
        // Check if user has profile image
        if (!$companyLogo) {
            return [
                'image' => 'Image not found',
            ];
        }

        // Send image url
        return [
            'company_logo_url' => $companyLogo,
        ];
    }
}
