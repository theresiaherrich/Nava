<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\QrSiswaController;
use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Pengelolaan data siswa
    Route::get('/siswa', [SiswaController::class, 'index'])
        ->middleware('role:ADMIN,BUDAYA_POSITIF');

    Route::post('/siswa', [SiswaController::class, 'store'])
        ->middleware('role:ADMIN,OPERATOR');

    // QR Code siswa
    Route::get('/siswa/{id_siswa}/qr', [QrSiswaController::class, 'show']);

    // Presensi
    Route::get('/presensi', [PresensiController::class, 'index'])
        ->middleware('role:ADMIN,BUDAYA_POSITIF');

    Route::get('/presensi/siswa/{id_siswa}', [PresensiController::class, 'riwayatSiswa']);

    Route::post('/presensi', [PresensiController::class, 'store']);
});