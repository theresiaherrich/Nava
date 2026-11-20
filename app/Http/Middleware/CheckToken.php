<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class CheckToken
{
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        $token = $request->bearerToken();

        if (!$token) {
            return redirect()->route('login');
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken || !$accessToken->tokenable) {
            return redirect()->route('login');
        }

        $user = $accessToken->tokenable;

        if (
            !empty($roles) &&
            !in_array($user->role, $roles, true)
        ) {
            abort(403, 'Kamu tidak memiliki akses ke halaman ini.');
        }

        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}