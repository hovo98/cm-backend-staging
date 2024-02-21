<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\User;

use App\Broker;
use App\Lender;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class ForceDeleteUser
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class ForceDeleteUser
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
        $input = collect($args)->toArray();
        $user_id = $input['id'];

        // Get User
        $user = User::withTrashed()->find($user_id);

        // Check role
        if ($user->role === 'lender') {
            $model = Lender::withTrashed()->find($user->id);
        } else {
            $model = Broker::withTrashed()->find($user->id);
        }

        $user = $model;

        // If there is no user return false
        if (! $user) {
            return [
                'success' => false,
                'message' => 'User is already deleted',
            ];
        }

        // Else delete User
        $user->forceDelete();

        return [
            'success' => true,
            'message' => 'User has been deleted',
        ];
    }
}
