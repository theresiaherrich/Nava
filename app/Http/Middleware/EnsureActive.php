<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureActive
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()?->is_active) {
            abort(403, 'Akun tidak aktif.');
        }
        return $next($request);
    }
}

