<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Company;

use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class UpdateCompany
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class UpdateCompany
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
        // Get currently logged in user
        $user = $context->user();

        $input = collect($args)->toArray();

        unset($input['directive']);
        // Get Company data
        $company_data = $user->getCompany();

        // Check data
        if ($company_data) {
            $input['company_name'] = $input['company_name'] ?? $company_data['company_name'];
            $input['domain'] = $company_data['domain'];
            $input['company_address'] = $input['company_address'] ?? $company_data['company_address'];
            $input['company_city'] = $input['company_city'] ?? $company_data['company_city'];
            $input['company_state'] = $input['company_state'] ?? $company_data['company_state'];
            $input['company_zip_code'] = $input['company_zip_code'] ?? $company_data['company_zip_code'];
            $input['company_phone'] = $input['company_phone'] ?? $company_data['company_phone'];
        }

        // If Company already exists save Company data in Users meta
        $user->updateCompanyData($input);

        return [
            'success' => true,
            'message' => 'Company information has been updated',
        ];
    }
}
