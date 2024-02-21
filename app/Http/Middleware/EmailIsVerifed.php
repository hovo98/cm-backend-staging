<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\AccountTemporarilyLockedException;
use App\Exceptions\EmailUnverifiedException;
use App\Exceptions\InvalidCredentialsException;
use App\User;
use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Redirect;

/**
 * Class EmailIsVerifed
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class EmailIsVerifed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $redirectToRoute
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        // Get email from query
        $query = $request->input('query');
        $queryArray = explode(':', $query);
        $userEmail = str_replace('"', '', explode('password', $queryArray[2])[0]);
        $email = strtolower(str_replace(',', '', $userEmail));

        // Get user
        $user = User::where('email', trim($email))->first();

        // The messages from the exceptions are not being used.
        // Error messages shown to the SPA for 403 status responses
        // are being set up in this class: \App\Http\Middleware\AttemptAuthentication
        if (! $user) {
            $trashedUser = User::where('email', trim($email))->onlyTrashed()->first();

            // Check if user is blocked by admin
            if ($trashedUser) {
                return $request->expectsJson()
                    ? throw new AccountTemporarilyLockedException('We\'re sorry, but this account was temporarily locked. Please refer to the email we sent you - we\'ll work with you to fix it ASAP.', 403)
                    : Redirect::route($redirectToRoute ?: 'verification.notice');
            }

            return $request->expectsJson()
                ? throw new InvalidCredentialsException('Incorrect username or password', 403)
                : Redirect::route($redirectToRoute ?: 'verification.notice');
        }

        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            return $request->expectsJson()
                ? throw new EmailUnverifiedException('Please verify your email', 403)
                : Redirect::route($redirectToRoute ?: 'verification.notice');
        }

        return $next($request);
    }
}
