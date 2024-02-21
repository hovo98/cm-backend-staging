<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\User;

use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class GetVerificationMail
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class GetVerificationMail
{
    /**
     * Return a value for the field.
     *
     * @param  null  $rootValue  Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param  mixed[]  $args  The arguments that were passed into the field.
     * @param  GraphQLContext  $context  Arbitrary data that is shared between all fields of a single query.
     * @param  ResolveInfo  $resolveInfo  Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return array
     */
    public function resolve($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $user = $context->user();

        // Check if user is already verified
        if ($user->email_verified_at) {
            return [
                'success' => false,
                'message' => 'You account has already been verified',
            ];
        }
        // Check if email is already sent in last hour
        $checkIfSholudSentEmail = $user->checkShouldVerifyEmailBeSent();
        if (! $checkIfSholudSentEmail) {
            return [
                'success' => false,
                'message' => 'Check your inbox for your verification link. Don\'t see it? Try again in ten minutes.',
            ];
        }

        // Else send email to user
        $user->sendEmailVerificationNotification();
        $model = User::find($user->id);
        $model->update(['sent_verify_email_at' => now()]);

        return [
            'success' => true,
            'message' => 'Verification email sent',
        ];
    }
}
