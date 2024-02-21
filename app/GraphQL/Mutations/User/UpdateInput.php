<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\User;

use App\Broker;
use App\Lender;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class UpdateInput
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class UpdateInput
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
        // Get currently logged in User
        $user = $context->user();

        if ($user->id != $args['id']) {
            return [
                'success' => false,
                'message' => 'This is not your profile.',
            ];
        }

        // Check role and return model by role
        if ($user->role === 'lender') {
            $model = Lender::find($user->id);
        } else {
            $model = Broker::find($user->id);
        }

        $context->user = $model;
        $user = $model;

        $input = collect($args)->toArray();
        unset($input['id']);

        // Update User
        $user->update($input);

        return [
            'success' => true,
            'message' => 'Your profile has been updated',
            'user' => $user,
        ];
    }
}
