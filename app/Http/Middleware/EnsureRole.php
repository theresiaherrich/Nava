<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memeriksa PERAN AKTIF (dipilih saat login / ganti peran), bukan sekadar semua peran.
 * Pemakaian: ->middleware('role:ADMIN,KURIKULUM')
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Belum login.'], 401);
        }

        $kode = $user->kodeRoles();
        $aktif = $request->session()->get('active_role');

        if (! $aktif && count($kode) === 1) {
            $aktif = $kode[0];
        }

        if (! $aktif || ! in_array($aktif, $kode, true) || ! in_array($aktif, $roles, true)) {
            return response()->json(['message' => 'Peran aktif Anda tidak memiliki akses ke halaman ini.'], 403);
        }

        $request->attributes->set('active_role', $aktif);

        return $next($request);
    }
}
