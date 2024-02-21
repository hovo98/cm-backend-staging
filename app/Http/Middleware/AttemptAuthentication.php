<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;

/**
 * Attempt to authenticate the user, but don't do anything if they are not.
 */
class AttemptAuthentication
{
    public const VERIFY_YOUR_ACCOUNT_MESSAGE = 'Verify your account to get started.';

    /**
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    public bool $authenticated;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @param  string  ...$guards
     * @return mixed Any kind of response
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $this->attemptAuthentication($guards);

        $response = $next($request);

        $content = $response->getOriginalContent();
        if ($this->isAccountTemporarilyLockedError($content)) {
            return $this->returnError('We\'re sorry, but this account was temporarily locked. Please refer to the email we sent you - we\'ll work with you to fix it ASAP.', 403);
        }
        if ($this->isInvalidCredentialsError($content)) {
            return $this->returnError('Incorrect username or password.', 403);
        }
        if ($this->isEmailVerificationError($content)) {
            return $this->returnError(self::VERIFY_YOUR_ACCOUNT_MESSAGE, 403);
        }

        if (data_get($content, 'errors.0.message') === 'Unauthenticated.') {
            return response()->json([
                'data' => null,
                'error' => 'Unauthenticated',
                'success' => false,
            ], 401);
        }

        if (data_get($content, 'errors.0.message') === 'Internal server error') {
            return response()->json([
                'data' => $content,
                'error' => 'Internal Server Error',
                'success' => false,
            ], 500);
        }

        return $response;
    }

    /**
     * @param  array<string>  ...$guards
     */
    protected function attemptAuthentication(array $guards): void
    {
        if (empty($guards)) {
            $guards = [config('lighthouse.guard')];
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                $this->auth->shouldUse($guard);
                return;
            }
        }
    }

    public function returnError(string $message, int $statusCode)
    {
        return response()->json([
            'errors' => [
                [
                    'extensions' => [
                        'reason' => $message,
                        'show_resend' => $message === self::VERIFY_YOUR_ACCOUNT_MESSAGE,
                    ],
                ],
            ],
        ], $statusCode);
    }

    public function isEmailVerificationError(array $content): bool
    {
        return data_get($content, 'errors.0.extensions.category') === 'email-unverified';
    }

    public function isInvalidCredentialsError(array $content): bool
    {
        return data_get($content, 'errors.0.extensions.category') === 'invalid-credentials';
    }

    public function isAccountTemporarilyLockedError(array $content): bool
    {
        return data_get($content, 'errors.0.extensions.category') === 'account-temporarily-locked';
    }
}
