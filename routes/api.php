
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\QrSiswaController;
use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::post('/lupa-password', [
    AuthController::class,
    'lupaPassword',
]);

Route::post('/reset-password', [
    AuthController::class,
    'resetPassword',
]);

Route::middleware('auth:sanctum')->group(function () {

    // Endpoint yang tetap dapat diakses sebelum mengganti password.
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/change-password', [
        AuthController::class,
        'changePassword',
    ]);

    // Fitur yang dilindungi middleware wajib ganti password.
    Route::middleware('must_change_password')->group(function () {

        // Pengelolaan data siswa
        Route::get('/siswa', [SiswaController::class, 'index'])
            ->middleware('role:ADMIN,BUDAYA_POSITIF,KURIKULUM,GURU,SISWA');

        Route::post('/siswa', [SiswaController::class, 'store'])
            ->middleware('role:ADMIN');

        // Template dan import Excel siswa
        Route::get('/siswa/template', [SiswaController::class, 'downloadTemplate'])
            ->middleware('role:ADMIN');

        Route::post('/siswa/import', [SiswaController::class, 'importExcel'])
            ->middleware('role:ADMIN');

        // QR Code siswa
        Route::get('/siswa/{id_siswa}/qr', [
            QrSiswaController::class,
            'show',
        ])->middleware('role:SISWA');

        // Presensi
        Route::get('/presensi/statistik', [PresensiController::class, 'statistik'])
            ->middleware('role:ADMIN,BUDAYA_POSITIF,KURIKULUM,GURU');

        Route::get('/presensi', [PresensiController::class, 'index'])
            ->middleware('role:ADMIN,BUDAYA_POSITIF,KURIKULUM,GURU');

        Route::get('/presensi/siswa/{id_siswa}', [
            PresensiController::class,
            'riwayatSiswa',
        ])->middleware('role:ADMIN,BUDAYA_POSITIF,KURIKULUM,GURU,SISWA');

        Route::post('/presensi', [PresensiController::class, 'store']);

        Route::post('/presensi/manual', [
            PresensiController::class,
            'tambahPresensiManual',
        ])->middleware('role:ADMIN,BUDAYA_POSITIF');

        Route::get('/presensi/rekap', [PresensiController::class, 'rekap'])
            ->middleware('role:ADMIN,BUDAYA_POSITIF,KURIKULUM,GURU');

        Route::get('/presensi/aturan', [PresensiController::class, 'aturan']);

        Route::patch('/presensi/{id_presensi}', [PresensiController::class, 'update'])
            ->middleware('role:ADMIN,GURU');

        Route::get('/presensi/{id_presensi}/riwayat-perubahan', [
            PresensiController::class,
            'riwayatPerubahan',
        ])->middleware('role:ADMIN,BUDAYA_POSITIF,KURIKULUM,GURU,SISWA');
    });
});
