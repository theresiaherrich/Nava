<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Selama must_change_password = true, hanya /auth/me, /auth/password/change,
 * dan /auth/logout yang boleh dipanggil (route tersebut tidak memakai middleware ini).
 */
class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->must_change_password) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return redirect()->route('password.change');
            }

            return response()->json([
                'message' => 'Anda wajib mengganti kata sandi sebelum melanjutkan.',
                'code' => 'MUST_CHANGE_PASSWORD',
            ], 403);
        }

        return $next($request);
    }
}
