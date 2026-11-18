<?php

use App\Exceptions\AturanBisnisException;
use App\Http\Middleware\EnsureAccountActive;
use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\JsonApi;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
            'account.active' => EnsureAccountActive::class,
            'password.changed' => EnsurePasswordChanged::class,
        ]);

        $middleware->prepend(JsonApi::class);

        $middleware->preventRequestForgery();

        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('api/*') ? null : route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $api = fn (Request $r) => $r->is('api/*') || $r->expectsJson();

        $exceptions->shouldRenderJsonWhen($api);

        // Format error seragam: { message, errors?, code? }
        $exceptions->render(function (AturanBisnisException $e, Request $r) use ($api) {
            if ($api($r)) {
                return response()->json(array_filter([
                    'message' => $e->getMessage(),
                    'code' => $e->kode,
                    'errors' => $e->errors ?: null,
                ]), $e->status);
            }

            return response()->view('errors.akademik', ['message' => $e->getMessage()], $e->status);
        });
        $exceptions->render(function (ValidationException $e, Request $r) use ($api) {
            if ($api($r)) {
                return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors(), 'code' => 'VALIDATION_ERROR'], $e->status);
            }
        });
        $exceptions->render(function (AuthenticationException $e, Request $r) use ($api) {
            if ($api($r)) {
                return response()->json(['message' => 'Silakan login terlebih dahulu.', 'code' => 'UNAUTHENTICATED'], 401);
            }
        });
        $exceptions->render(function (AuthorizationException $e, Request $r) use ($api) {
            if ($api($r)) {
                return response()->json(['message' => $e->getMessage() ?: 'Anda tidak memiliki akses.', 'code' => 'FORBIDDEN'], 403);
            }
        });
        $exceptions->render(function (NotFoundHttpException $e, Request $r) use ($api) {
            if ($api($r)) {
                $model = $e->getPrevious() instanceof ModelNotFoundException;

                return response()->json(['message' => $model ? 'Data tidak ditemukan.' : ($e->getMessage() ?: 'Endpoint tidak ditemukan.'), 'code' => 'NOT_FOUND'], 404);
            }
        });
        $exceptions->render(function (UniqueConstraintViolationException $e, Request $r) use ($api) {
            if ($api($r)) {
                return response()->json(['message' => 'Data duplikat. Muat ulang dan periksa data yang sudah ada.', 'code' => 'DUPLICATE'], 409);
            }
        });
        $exceptions->render(function (TokenMismatchException $e, Request $r) use ($api) {
            if ($api($r)) {
                return response()->json(['message' => 'Sesi atau token CSRF tidak valid. Ambil ulang /api/csrf-token.', 'code' => 'CSRF_MISMATCH'], 419);
            }
        });
        $exceptions->render(function (ThrottleRequestsException $e, Request $r) use ($api) {
            if ($api($r)) {
                return response()->json(['message' => 'Terlalu banyak percobaan. Coba lagi sebentar lagi.', 'code' => 'TOO_MANY_REQUESTS'], 429, $e->getHeaders());
            }
        });
        $exceptions->render(function (HttpExceptionInterface $e, Request $r) use ($api) {
            if ($api($r) && $e->getStatusCode() === 403) {
                return response()->json(['message' => $e->getMessage() ?: 'Anda tidak memiliki akses.', 'code' => 'FORBIDDEN'], 403);
            }
        });
    })->create();
