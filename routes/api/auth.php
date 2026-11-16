<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Support\Facades\Route;

Route::get('/csrf-token', fn () => response()->json(['data' => ['csrf_token' => csrf_token()], 'message' => null]));

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

    // Tetap bisa diakses walau must_change_password = true.
    Route::middleware(['auth', 'account.active'])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/password/change', [PasswordController::class, 'change'])->middleware('throttle:10,1');
    });

    Route::middleware(['auth', 'account.active', 'password.changed'])
        ->post('/switch-role', [AuthController::class, 'switchRole']);
});
