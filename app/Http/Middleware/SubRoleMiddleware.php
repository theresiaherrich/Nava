<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class SubRoleMiddleware {
    public function handle(Request $request, Closure $next, string ...$subRoles): Response {
        $user = $request->user();
        abort_unless($user && $user->is_active && in_array($user->sub_role, $subRoles, true), 403);
        return $next($request);
    }
}