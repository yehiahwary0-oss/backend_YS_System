<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CookieToBearer
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            ! $request->bearerToken()
            && $request->hasCookie('ys_admin_token')
        ) {
            $request->headers->set(
                'Authorization',
                'Bearer ' . $request->cookie('ys_admin_token')
            );
        }

        return $next($request);
    }
}
