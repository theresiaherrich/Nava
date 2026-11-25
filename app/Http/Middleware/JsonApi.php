<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Memaksa respons JSON untuk /api dan mencegah cache data pribadi. */
class JsonApi
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('api/*')) {
            $request->headers->set('Accept', 'application/json');
        }

        $response = $next($request);

        if ($request->is('api/*') || ($request->hasSession() && $request->user())) {
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        return $response;
    }
}
