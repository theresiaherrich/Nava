<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrangTuaVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $idUser = $request->session()->get('ortu_id_user');

        $relation = DB::table('orang_tua')
            ->where('id_user', $idUser)
            ->where('status_verifikasi', 'verified')
            ->orderBy('id_siswa')
            ->first();

        if (!$relation) {
            return redirect()->route('ortu.status');
        }

        // Pastikan siswa yang diakses memang anak dari akun ini.
        $activeStudent = $request->session()->get('portal_siswa_id');

        if (!$activeStudent) {
            $request->session()->put(
                'portal_siswa_id',
                $relation->id_siswa
            );
        } else {
            $authorized = DB::table('orang_tua')
                ->where('id_user', $idUser)
                ->where('id_siswa', $activeStudent)
                ->where('status_verifikasi', 'verified')
                ->exists();

            if (!$authorized) {
                $request->session()->put(
                    'portal_siswa_id',
                    $relation->id_siswa
                );
            }
        }

        return $next($request);
    }
}
