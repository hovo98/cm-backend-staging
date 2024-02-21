<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\User;

use App\Broker;
use App\Lender;
use App\Mail\ErrorEmail;
use App\Mail\InvitationEmailBrokerToBroker;
use App\Mail\InvitationEmailBrokerToLender;
use App\Mail\InvitationEmailLenderToBroker;
use App\Mail\InvitationEmailLenderToLender;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Mail;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class InvitationEmail
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class InvitationEmailResolver
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
        $sender = $context->user();
        $input = collect($args)->toArray();
        $input = $input['input'];

        // Get user name
        $senderRole = $sender->role;
        $errorsResponseBroker = [];
        $errorsResponseLender = [];

        foreach ($input['inviteBroker'] as $emailBrokerToBeInvited) {
            $brokerTobeInvited = Broker::where('email', $emailBrokerToBeInvited)->withTrashed()->first();
            if (! $emailBrokerToBeInvited || ($brokerTobeInvited && $brokerTobeInvited->trashed())) {
                continue;
            }

            // Get email from args
            $emailBrokerToBeInvitedCheck = filter_var(trim($emailBrokerToBeInvited), FILTER_VALIDATE_EMAIL);

            // If there is no email continue
            if (! $emailBrokerToBeInvitedCheck) {
                $errorsResponseBroker[] = [
                    'email' => $emailBrokerToBeInvited,
                    'message' => 'Please enter a valid email',
                ];

                continue;
            }
            if ($senderRole === 'broker') {
                try {
                    Mail::to($emailBrokerToBeInvited)->send(new InvitationEmailBrokerToBroker($sender));
                } catch (\Throwable $exception) {
                    Mail::send(new ErrorEmail($emailBrokerToBeInvited, 'User sent invitation email to another user', $exception));
                }
            } else {
                try {
                    Mail::to($emailBrokerToBeInvited)->send(new InvitationEmailLenderToBroker($sender));
                } catch (\Throwable $exception) {
                    Mail::send(new ErrorEmail($emailBrokerToBeInvited, 'User sent invitation email to another user', $exception));
                }
            }
        }

        foreach ($input['inviteLender'] as $emailLenderToBeInvited) {
            $lenderToBeInvited = Lender::where('email', $emailLenderToBeInvited)->withTrashed()->first();
            if (! $emailLenderToBeInvited || ($lenderToBeInvited && $lenderToBeInvited->trashed())) {
                continue;
            }

            // Get email from args
            $emailLenderToBeInvitedCheck = filter_var(trim($emailLenderToBeInvited), FILTER_VALIDATE_EMAIL);

            // If there is no email continue
            if (! $emailLenderToBeInvitedCheck) {
                $errorsResponseLender[] = [
                    'email' => $emailLenderToBeInvited,
                    'message' => 'Please enter a valid email',
                ];

                continue;
            }

            if ($senderRole === 'lender') {
                try {
                    Mail::to($emailLenderToBeInvited)->send(new InvitationEmailLenderToLender($sender));
                } catch (\Throwable $exception) {
                    Mail::send(new ErrorEmail($emailLenderToBeInvited, 'User sent invitation email to another user', $exception));
                }
            } else {
                try {
                    Mail::to($emailLenderToBeInvited)->send(new InvitationEmailBrokerToLender($sender));
                } catch (\Throwable $exception) {
                    Mail::send(new ErrorEmail($emailLenderToBeInvited, 'User sent invitation email to another user', $exception));
                }
            }
        }

        return [
            'errorsResponseBroker' => $errorsResponseBroker,
            'errorsResponseLender' => $errorsResponseLender,
        ];
    }
}
