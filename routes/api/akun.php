<?php

use App\Http\Controllers\Akun\AkunController;
use App\Http\Controllers\Akun\AkunMassalController;
use App\Http\Controllers\Akun\AuditLogController;
use Illuminate\Support\Facades\Route;

// Batas peran yang dapat dikelola tiap peran aktif: config('siakad.role_manageable').
Route::middleware('role:SUPERADMIN,ADMIN')->group(function () {
    Route::get('/roles', [AkunController::class, 'roles']);
    Route::get('/akun', [AkunController::class, 'index']);
    Route::post('/akun', [AkunController::class, 'store']);
    Route::post('/akun/massal', [AkunMassalController::class, 'store']);
    Route::get('/akun/{user}', [AkunController::class, 'show']);
    Route::put('/akun/{user}', [AkunController::class, 'update']);
    Route::put('/akun/{user}/peran', [AkunController::class, 'setPeran']);
    Route::patch('/akun/{user}/status', [AkunController::class, 'setStatus']);
    Route::post('/akun/{user}/reset-password', [AkunController::class, 'resetPassword']);
    Route::get('/audit-log', [AuditLogController::class, 'index']);
});
