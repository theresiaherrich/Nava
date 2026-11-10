<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * RoleMiddleware — melindungi route berdasarkan role pengguna.
 *
 * Penggunaan di routes:
 *   ->middleware('role:SISWA')
 *   ->middleware('role:GURU')   (Guru BK — GURU yang ada di guru_bk)
 *   ->middleware('role:ADMIN')
 *
 * Role GURU BK: user.role = 'GURU' DAN ada row di tabel guru_bk.
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $userRole = strtoupper($user->role);

        foreach ($roles as $role) {
            $role = strtoupper($role);

            if ($role === 'GURU') {
                // GURU BK: role 'GURU' + ada di tabel guru_bk
                if ($userRole === 'GURU' && $this->isGuruBk($user->id_user)) {
                    return $next($request);
                }
            } elseif ($role === $userRole) {
                return $next($request);
            }
        }

        // Redirect ke halaman yang sesuai rolenya jika sudah login tapi role salah
        return $this->redirectToHome($userRole, $user->id_user);
    }

    private function isGuruBk(int $idUser): bool
    {
        return DB::table('guru_bk')
            ->join('guru', 'guru.id_guru', '=', 'guru_bk.id_guru')
            ->where('guru.id_user', $idUser)
            ->exists();
    }

    private function redirectToHome(string $role, int $idUser): Response
    {
        if ($role === 'SISWA') {
            return redirect()->route('siswa.dashboard');
        }
        if ($role === 'GURU' && $this->isGuruBk($idUser)) {
            return redirect()->route('bk.dashboard');
        }
        if ($role === 'ADMIN') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('login')->with('error', 'Akses tidak diizinkan.');
    }
}
