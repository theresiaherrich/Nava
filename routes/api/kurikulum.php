<?php

use App\Http\Controllers\Kurikulum\MapelController;
use App\Http\Controllers\Kurikulum\RombelController;
use App\Http\Controllers\Kurikulum\TahunAjaranController;
use App\Http\Controllers\Kurikulum\WaliKelasController;
use Illuminate\Support\Facades\Route;

Route::prefix('kurikulum')->group(function () {
    // Referensi akademik dibaca juga oleh Operator & Kesiswaan.
    Route::middleware('role:KURIKULUM,ADMIN,KESISWAAN')->group(function () {
        Route::get('/tahun-ajaran', [TahunAjaranController::class, 'index']);
        Route::get('/mapel', [MapelController::class, 'index']);
    });

    Route::middleware('role:KURIKULUM')->group(function () {
        Route::post('/tahun-ajaran', [TahunAjaranController::class, 'store']);
        Route::post('/semester/{semester}/aktifkan', [TahunAjaranController::class, 'aktifkanSemester']);

        Route::post('/mapel', [MapelController::class, 'store']);
        Route::put('/mapel/{mapel}', [MapelController::class, 'update']);

        Route::get('/rombel', [RombelController::class, 'index']);
        Route::post('/rombel', [RombelController::class, 'store']);
        Route::get('/rombel/{rombel}', [RombelController::class, 'show']);
        Route::put('/rombel/{rombel}', [RombelController::class, 'update']);
        Route::delete('/rombel/{rombel}', [RombelController::class, 'destroy']);
        Route::put('/rombel/{rombel}/guru', [RombelController::class, 'setGuru']);
        Route::post('/rombel/{rombel}/peserta', [RombelController::class, 'tambahPeserta']);
        Route::delete('/rombel/{rombel}/peserta/{peserta}', [RombelController::class, 'hapusPeserta']);

        Route::get('/wali-kelas', [WaliKelasController::class, 'index']);
        Route::put('/kelas/{kelas}/wali', [WaliKelasController::class, 'store']);
        Route::delete('/wali-kelas/{waliKelas}', [WaliKelasController::class, 'destroy']);
    });
});
