<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Authenticate extends Middleware
{

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure $next
     * @param  string[]  ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards): Response
    {
        if (in_array('api', $guards) && Auth::guard('api')->check()) {
            return $next($request);
        }

        if (in_array('share', $guards)) {
            if (Auth::guard('share')->check()) {
                config()->set('auth.defaults.guard', 'share');
                return $next($request);
            }
        }

        return response('', Response::HTTP_UNAUTHORIZED);
    }
}
