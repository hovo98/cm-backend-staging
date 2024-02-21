<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

/**
 * Class CheckLender
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class CheckLender
{
    /**
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed|void
     *
     * @throws AuthorizationException
     */
    public function handle($request, Closure $next)
    {
        if (! $request->user() || $request->user()->role !== 'lender') {
            throw new AuthorizationException('You are not Lender.');
        }

        return $next($request);
    }
}
