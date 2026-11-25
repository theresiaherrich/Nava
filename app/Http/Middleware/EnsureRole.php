<?php

namespace App\Http\Middleware;

use App\Support\AcademicAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memeriksa PERAN AKTIF (dipilih saat login / "Ganti Peran"), bukan gabungan semua peran.
 * Pemakaian: ->middleware('role:KURIKULUM,ADMIN')
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return response()->json(['message' => 'Silakan login terlebih dahulu.', 'code' => 'UNAUTHENTICATED'], 401);
        }

        $aktif = AcademicAccess::peranAktif();

        if ($aktif === null || ! in_array($aktif, $roles, true)) {
            return response()->json([
                'message' => 'Peran aktif Anda tidak memiliki akses ke fitur ini.',
                'code' => 'ROLE_FORBIDDEN',
            ], 403);
        }

        return $next($request);
    }
}
