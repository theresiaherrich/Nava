<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * - Akun nonaktif/pending langsung dikeluarkan.
 * - Akun yang wajib ganti kata sandi hanya boleh memanggil endpoint /api/auth/*.
 */
class EnsureAccountActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Belum login.'], 401);
        }

        if (! $user->isAktif()) {
            Auth::logout();
            $request->session()->invalidate();

            return response()->json(['message' => 'Akun tidak aktif. Hubungi operator sekolah.'], 403);
        }

        if ($user->must_change_password && ! $request->is('api/auth/*')) {
            return response()->json([
                'message' => 'Ganti kata sandi terlebih dahulu.',
                'code' => 'must_change_password',
            ], 423);
        }

        return $next($request);
    }
}
