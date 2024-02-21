<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Company;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class UpdateCompanyLogo
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class UpdateCompanyLogo
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
        // Get currently logged in User
        $user = $context->user();

        // Check if image exists
        if (isset($args['company_logo'])) {
            // If User already have image, delete from folder before update
            if ($user->getCompany()['company_logo']) {
                Storage::delete($user->getCompany()['company_logo']);
            }

            /** @var \Illuminate\Http\UploadedFile $file */
            $file = $args['company_logo'];

            // Resize image
            Image::make($file->path())->fit(150)->save();

            // Store image into folder and get path
            $path = $file->storePublicly('company-logos', config('app.app_image_upload'));

            // Store path of image into user table
            $user->updateCompanyData([
                'company_logo' => $path,
            ]);
        }

        return [
            'success' => true,
            'message' => 'Company logo updated.',
            'company_logo_url' => $user->getCompany()['company_logo'],
        ];
    }

    /**
     * @param $rootValue
     * @param  array  $args
     * @param  GraphQLContext|null  $context
     * @param  ResolveInfo  $resolveInfo
     * @return array
     */
    public function delete($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        // Get currently logged in User
        $user = $context->user();

        // Check if image exists
        if ($user->getCompany()['company_logo']) {
            // Get endpoint
            if (config('filesystems.default') === 's3') {
                $endpointToSlice = config('filesystems.disks.do.endpoint');
            } else {
                $endpointToSlice = config('app.asset_url');
            }

            // Remove endpoint from image path
            $imagePath = str_replace($endpointToSlice, '', $user->getCompany()['company_logo']);
            //Remove image
            Storage::disk(config('app.app_image_upload'))->delete($imagePath);

            // Store path of image into user table
            $user->updateCompanyData([
                'company_logo' => null,
            ]);
        }

        return [
            'success' => true,
            'message' => 'Company logo deleted.',
        ];
    }
}
