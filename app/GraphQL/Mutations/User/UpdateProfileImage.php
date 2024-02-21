<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\User;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class UpdateProfileImage
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class UpdateProfileImage
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
        if (isset($args['profile_image'])) {
            // If User already have image, delete from folder before update
            if ($user->profile_image) {
                Storage::disk(config('app.app_image_upload'))->delete($user->profile_image);
            }

            /** @var \Illuminate\Http\UploadedFile $file */
            $file = $args['profile_image'];

            // Resize image
            Image::make($file->path())->fit(150)->save();

            // Store image into folder and get path
            $path = $file->storePublicly('profile-images', config('app.app_image_upload'));

            // Store path of image into user table
            $user->update([
                'profile_image' => $path,
            ]);
        }

        return [
            'success' => true,
            'message' => 'Profile image updated',
            'user' => $user,
            'image' => $user->profile_image,
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
        if ($user->profile_image) {
            // Get endpoint
            if (config('filesystems.default') === 's3') {
                $endpointToSlice = config('filesystems.disks.do.endpoint');
            } else {
                $endpointToSlice = config('app.asset_url');
            }

            // Remove form image path
            $imagePath = str_replace($endpointToSlice, '', $user->profile_image);
            // Delete
            Storage::disk(config('app.app_image_upload'))->delete($imagePath);

            // Store path of image into user table
            $user->update([
                'profile_image' => null,
            ]);
        }

        return [
            'success' => true,
            'message' => 'Profile image deleted',
            'user' => $user,
        ];
    }
}
