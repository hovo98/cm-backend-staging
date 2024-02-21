<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

/**
 * Class CheckBroker
 *
 * @author Milica Mihajlovic <milica@forwardslashny.com>
 */
class CheckBroker
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
        if (! $request->user() || $request->user()->getAttribute('role') !== 'broker') {
            throw new AuthorizationException('You are not Broker.');
        }

        return $next($request);
    }
}
