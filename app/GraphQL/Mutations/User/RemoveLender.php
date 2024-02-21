<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\User;

use App\Broker;
use App\Lender;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\DB;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class RemoveLender
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class RemoveLender
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

        if ($user->role !== 'broker') {
            return [
                'success' => false,
                'message' => 'Only Broker can remove Lenders.',
            ];
        }
        $broker = Broker::find($user->id);
        $context->user = $broker;
        $user = $broker;

        // Check if Lender exists
        $lender_email = $args['lender_email'];
        $lender = Lender::where('email', $lender_email)->first();

        if (! $lender) {
            $existsConnection = DB::table('broker_lender_email')
                ->where('broker_id', $user->id)
                ->where('email', $lender_email)
                ->first();

            if (! $existsConnection) {
                return [
                    'success' => false,
                    'message' => 'Lender doesn\'t exist in connections.',
                ];
            }

            $user->lenderEmails()->wherePivot('email', $lender_email)->detach();

            return [
                'success' => true,
                'message' => 'Lender is removed from Broker\'s connections.',
            ];
        }

        // Check role
        if ($lender->role !== 'lender') {
            return [
                'success' => false,
                'message' => 'Lender doesn\'t exist.',
            ];
        }

        // Detach Lender from Broker
        $user->lenders()->detach($lender->id);

        return [
            'success' => true,
            'message' => 'Lender is removed from Broker\'s connections.',
        ];
    }
}
