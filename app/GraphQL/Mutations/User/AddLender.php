<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\User;

use App\Broker;
use App\Lender;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\DB;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class AddLender
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class AddLender
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
        // Get currently logged in user
        $user = $context->user();

        if ($user->role !== 'broker') {
            return [
                'success' => false,
                'message' => 'Only Broker can add Lenders',
            ];
        }

        // Get Broker
        $broker = Broker::find($user->id);
        $context->user = $broker;
        $user = $broker;
        $errorsResponse = [];

        foreach ($args['emails'] as $email) {
            // Get email from args
            $emailChecked = filter_var(trim($email), FILTER_VALIDATE_EMAIL);

            // If there is no email continue
            if (! $emailChecked) {
                $errorsResponse[] = [
                    'email' => $email,
                    'message' => 'Please enter a valid email',
                ];

                continue;
            }
            // Get domain from email
            $emailDomainOnly = preg_replace('/.+@/', '', $emailChecked);
            $invitationTable = $user->checkInvitationTable($user->id, $emailDomainOnly);
            $pivot_table = $user->checkPivotTable($user->id, $emailDomainOnly);
            $lender = Lender::where('email', '=', $emailChecked)->first();

            if (! $lender) {
                $existsConnection = DB::table('broker_lender_email')
                    ->where('broker_id', $user->id)
                    ->where('email', $email)
                    ->first();

                if ($existsConnection) {
                    $errorsResponse[] = [
                        'email' => $emailChecked,
                        'message' => 'You already added this lender',
                    ];

                    continue;
                }
                if ($invitationTable || $pivot_table) {
                    $errorsResponse[] = [
                        'email' => $emailChecked,
                        'message' => 'You already added a lender from this bank',
                    ];

                    continue;
                }

                // Else attach email to Broker
                $user->lenderEmails()->save($user, ['email' => $email]);

                continue;
            }

            // If Lender exists
            if ($lender->role === 'broker') {
                $errorsResponse[] = [
                    'email' => $emailChecked,
                    'message' => 'The user has to have role Lender',
                ];

                continue;
            }
            // Check if Lender is already added to Broker
            $exists = DB::table('broker_lender')
                ->where('broker_id', $user->id)
                ->where('lender_id', $lender->id)
                ->first();

            if ($exists) {
                $errorsResponse[] = [
                    'email' => $emailChecked,
                    'message' => 'You already added this lender',
                ];

                continue;
            }
            if ($invitationTable || $pivot_table) {
                $errorsResponse[] = [
                    'email' => $emailChecked,
                    'message' => 'You already added a lender from this bank',
                ];

                continue;
            }

            // Add Lender to Broker
            $user->lenders()->attach($lender->id);

            continue;
        }

        return $errorsResponse;
    }
}
