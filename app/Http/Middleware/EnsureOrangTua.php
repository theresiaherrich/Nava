<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrangTua
{
    public function handle(Request $request, Closure $next): Response
    {
        $idUser = $request->session()->get('ortu_id_user');

        if (!$idUser) {
            return redirect()->route('ortu.login');
        }

        $valid = DB::table('user')
            ->where('id_user', $idUser)
            ->where('role', 'ORANG_TUA')
            ->exists();

        if (!$valid) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('ortu.login');
        }

        return $next($request);
    }
}
