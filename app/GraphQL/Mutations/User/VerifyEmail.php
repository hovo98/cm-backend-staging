<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\User;

use App\Lender;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Auth\Events\Verified;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Joselfonseca\LighthouseGraphQLPassport\Exceptions\ValidationException;
use Joselfonseca\LighthouseGraphQLPassport\GraphQL\Mutations\VerifyEmail as JoselfonsecaVerifyEmail;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

/**
 * Class VerifyEmail
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class VerifyEmail extends JoselfonsecaVerifyEmail
{
    /**
     * @param $rootValue
     * @param  array  $args
     * @param  \Nuwave\Lighthouse\Support\Contracts\GraphQLContext|null  $context
     * @param  \GraphQL\Type\Definition\ResolveInfo  $resolveInfo
     * @return array
     *
     * @throws \Exception
     */
    public function resolve($rootValue, array $args, GraphQLContext $context = null, ResolveInfo $resolveInfo)
    {
        // Decrypt email
        try {
            $payload = decrypt($args['email'], true);
        } catch (\Throwable $th) {
            Log::info($th);
            Log::info($args['email']);
            throw new ValidationException([
                'token' => __('The token is not valid, please try again'),
            ], 'Validation Error');
        }

        // Check expiration
        if (Carbon::parse($payload['expiration']) < now()) {
            throw new ValidationException([
                'token' => __('The token is expired.'),
            ], 'Validation Error');
        }

        // Get email
        $email = filter_var(trim($payload['email']), FILTER_VALIDATE_EMAIL);

        $model = app(config('auth.providers.users.model'));

        try {
            // Get User based on email
            $user = $model->where('email', $email)->firstOrFail();
            // Verify email for that User
            $user->markEmailAsVerified();
            event(new Verified($user));
            Auth::onceUsingId($user->id);
            // Get tokens for User
            $tokens = $user->getTokens();
            $tokens['user'] = $user;
            $tempToken = '';

            if ($user->role === 'lender') {
                $tempToken = $this->checkToken($user);
            }

            return array_merge(
                $tokens,
                [
                    'tempToken' => $tempToken,
                ]
            );
        } catch (ModelNotFoundException $e) {
            throw new ValidationException([
                'email' => __('Please enter a valid email'),
            ], 'Validation Error');
        }
    }

    private function checkToken($user)
    {
        $lender = Lender::find($user->id);
        $preferences = $lender->getPerfectFit();
        $tempToken = '';

        if (! $preferences) {
            $tempToken = encrypt([
                'id' => $user->id,
                'email' => $user->email,
            ]);
        }

        return $tempToken;
    }
}
