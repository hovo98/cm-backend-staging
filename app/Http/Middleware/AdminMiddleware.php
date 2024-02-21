<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

/**
 * Class AdminMiddleware
 *
 * Prevents access to admin dashboard to non-logged and non-admin users.
 *
 * @author Boris Djemrovski <boris@forwardslashny.com>
 */
class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $request->user() || $request->user()->role !== 'admin') {
            Auth::logout();

            return redirect('login');
        }

        View::share('currentUser', Auth::user());

        return $next($request);
    }
}
