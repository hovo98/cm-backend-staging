<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Support\Facades\Redirect;

/**
 * Class BetaUser
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class BetaUser
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
        // Get user from request
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson()
                ? abort(403, 'Incorrect username or password')
                : Redirect::route($redirectToRoute ?: 'verification.notice');
        }

        $allowedUsers = User::allowedUsersBeta($user->email);
        if (! $allowedUsers) {
            return $request->expectsJson()
                ? abort(403, 'We\'re sorry, login temporarily limited to BETA users.')
                : Redirect::route($redirectToRoute ?: 'verification.notice');
        }

        return $next($request);
    }
}
