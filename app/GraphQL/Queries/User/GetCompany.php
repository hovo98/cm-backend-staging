<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\User;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class GetCompany
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class GetCompany
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

        // Get company data for User
        $company = $user->getCompany();

        // Set value for company logo
        $company['company_logo'] = $company['company_logo'] ?? '';

        // Check if there is company logo
        if (! $company['company_logo']) {
            $image_url = '';
        } else {
            // If there is return image url
            $image_url = asset('storage/app/'.$company['company_logo']);
            unset($company['company_logo']);
        }
        $company['company_logo_url'] = $image_url;

        // Check all data
        $company['company_name'] = $company['company_name'] ?? '';
        // $company['domain'] = $company['domain'] ?? '';
        $company['company_address'] = $company['company_address'] ?? '';
        $company['company_city'] = $company['company_city'] ?? '';
        $company['company_state'] = $company['company_state'] ?? '';
        $company['company_zip_code'] = $company['company_zip_code'] ?? '';
        $company['company_phone'] = $company['company_phone'] ?? '';
        $company['company_logo_url'] = $company['company_logo_url'] ?? '';

        // Return Company data
        return [
            'company_data' => $company,
        ];
    }
}
