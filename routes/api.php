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
        ->middleware('role:ADMIN,BUDAYA_POSITIF,KURIKULUM,GURU,SISWA');

    Route::post('/siswa', [SiswaController::class, 'store'])
        ->middleware('role:ADMIN');

    // QR Code siswa
    Route::get('/siswa/{id_siswa}/qr', [QrSiswaController::class, 'show']);

    // Presensi
    // Statistik dashboard presensi
    Route::get('/presensi/statistik', [PresensiController::class, 'statistik'])
        ->middleware('role:ADMIN,BUDAYA_POSITIF,KURIKULUM,GURU');

    Route::get('/presensi', [PresensiController::class, 'index'])
        ->middleware('role:ADMIN,BUDAYA_POSITIF,KURIKULUM,GURU');

    Route::get('/presensi/siswa/{id_siswa}', [PresensiController::class, 'riwayatSiswa']);

    Route::post('/presensi', [PresensiController::class, 'store']);

    Route::post('/presensi/manual', [
        PresensiController::class,
        'tambahPresensiManual',
    ])->middleware('role:ADMIN,BUDAYA_POSITIF');

    Route::get('/presensi/rekap', [PresensiController::class, 'rekap'])
        ->middleware('role:ADMIN,BUDAYA_POSITIF,KURIKULUM,GURU');

    Route::get('/presensi/aturan', [PresensiController::class, 'aturan']);

    Route::patch('/presensi/{id_presensi}', [PresensiController::class, 'update'])
        ->middleware('role:ADMIN');

    Route::get('/presensi/{id_presensi}/riwayat-perubahan', [
        PresensiController::class,
        'riwayatPerubahan',
    ]);
});