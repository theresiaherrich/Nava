<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('admin_id_user')) {
            return redirect()->route('admin.login');
        }

        $admin = \Illuminate\Support\Facades\DB::table('user')
            ->where('id_user', $request->session()->get('admin_id_user'))
            ->where('role', 'ADMIN')
            ->exists();

        if (! $admin) {
            $request->session()->forget(['admin_id_user', 'admin_username']);
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
