<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(Route::has('preview') ? 'preview' : 'dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')->name('login.attempt');
    Route::get('/forgot-password', [PasswordController::class, 'forgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordController::class, 'sendResetLink'])
        ->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordController::class, 'resetPassword'])->name('password.update');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::put('/password', [PasswordController::class, 'changePassword'])->name('password.change');
    Route::post('/admin/users', [UserController::class, 'store'])
        ->middleware('can:users.create')->name('admin.users.store');
});

// Halaman frontend per peran (Operator, Kurikulum, Guru, Wali Kelas, Siswa) + /preview.
require __DIR__.'/frontend.php';
