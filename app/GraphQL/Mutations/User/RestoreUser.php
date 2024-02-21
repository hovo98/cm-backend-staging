<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\User;

use App\Broker;
use App\Lender;
use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class RestoreUser
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class RestoreUser
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

        // Find User in trash
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
                'message' => 'The user is not in trash.',
            ];
        }

        // Else restore user with his data
        $user->restore();

        return [
            'success' => true,
            'message' => 'The user is restored.',
        ];
    }
}
