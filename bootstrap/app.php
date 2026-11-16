<?php

use App\Http\Middleware\EnsureAccountActive;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\EnsureActive;
use App\Http\Middleware\JsonApi;
use App\Http\Middleware\RequireRole;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.'/../routes/web.php', commands: __DIR__.'/../routes/console.php', health: '/up')
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
            'account.active' => EnsureAccountActive::class,
        ]);
        $middleware->validateCsrfTokens(
            except: (
                env('APP_ENV') === 'local'
                && filter_var(env('SIAKAD_DEV_SKIP_CSRF', false), FILTER_VALIDATE_BOOLEAN)
            ) ? ['api/*'] : []
        );

        // Tamu yang belum login mendapat 401 JSON, bukan redirect ke route "login" (tidak ada di API).
        $middleware->redirectGuestsTo(fn () => null);
        $middleware->prepend(JsonApi::class);
        $middleware->alias(['active' => EnsureActive::class, 'role' => RequireRole::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn (Request $r) => $r->is('api/*') || $r->expectsJson());
        $exceptions->render(function (UniqueConstraintViolationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json(['message' => 'Data duplikat. Muat ulang dan periksa data yang sudah ada.'], 409);
            }
        });
    })->create();
