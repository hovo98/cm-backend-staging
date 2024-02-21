<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\User;

use App\User;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Password;
use Joselfonseca\LighthouseGraphQLPassport\GraphQL\Mutations\ResetPassword as JoselfonsecaResetPassword;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class ResetPassword
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
class ResetPassword extends JoselfonsecaResetPassword
{
    /**
     * {@inheritDoc}
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        $payload = decrypt($args['token'], true);
        $args['token'] = $payload['token'];
        $args['email'] = $payload['email'];

        $args = collect($args)->except('directive', 'recaptcha')->toArray();
        $response = $this->broker()->reset($args, function ($user, $password) {
            $this->resetPassword($user, $password);
        });

        if ($response === Password::PASSWORD_RESET) {
            // Find User
            $user = User::where('email', '=', $args['email'])->first();

            $user->sendUpdatePasswordConfirmation();

            return [
                'status' => 'PASSWORD_UPDATED',
                'message' => __($response),
            ];
        }

        return [
            'status' => 'PASSWORD_NOT_UPDATED',
            'message' => __($response),
        ];

        // return parent::resolve($rootValue, $args, $context, $resolveInfo);
    }
}
