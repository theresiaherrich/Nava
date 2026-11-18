<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MustChangePassword
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Aturan wajib ganti password hanya berlaku untuk SISWA.
        if ($user->role === 'SISWA' && $user->must_change_password) {
            return response()->json([
                'message' => 'Anda harus mengganti password awal sebelum mengakses fitur ini.',
                'must_change_password' => true,
            ], 403);
        }

        return $next($request);
    }
}
