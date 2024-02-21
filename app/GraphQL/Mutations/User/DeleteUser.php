<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\User;

use App\Broker;
use App\Lender;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class DeleteUser
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class DeleteUser
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
        $user = User::find($user_id);

        // Check role
        if ($user->role === 'lender') {
            $model = Lender::find($user->id);
        } else {
            $model = Broker::find($user->id);
        }

        $user = $model;

        // If there is no user return false
        if (! $user) {
            return [
                'success' => false,
                'message' => 'The user is already in trash or already deleted, if you want to delete user go with force delete.',
            ];
        }

        // Else delete User
        $user->delete();

        return [
            'success' => true,
            'message' => 'User has been deleted',
        ];
    }
}
