<?php

use App\Http\Controllers\Master\DashboardOperatorController;
use App\Http\Controllers\Master\GuruController;
use App\Http\Controllers\Master\ImporSiswaController;
use App\Http\Controllers\Master\KelasController;
use App\Http\Controllers\Master\OrangTuaController;
use App\Http\Controllers\Master\SekolahController;
use App\Http\Controllers\Master\SiswaController;
use Illuminate\Support\Facades\Route;

Route::get('/referensi/agama', [SekolahController::class, 'agama']);

Route::prefix('master')->group(function () {
    // Baca: Operator, Kurikulum (rombel/wali), Kesiswaan (peserta ekskul).
    Route::middleware('role:ADMIN,KURIKULUM,KESISWAAN')->group(function () {
        Route::get('/sekolah', [SekolahController::class, 'show']);
        Route::get('/siswa', [SiswaController::class, 'index']);
        Route::get('/siswa/{siswa}', [SiswaController::class, 'show']);
        Route::get('/guru', [GuruController::class, 'index']);
        Route::get('/guru/{guru}', [GuruController::class, 'show']);
        Route::get('/kelas', [KelasController::class, 'index']);
        Route::get('/kelas/{kelas}/anggota', [KelasController::class, 'anggota']);
    });

    // Tulis: Operator saja.
    Route::middleware('role:ADMIN')->group(function () {
        Route::get('/dashboard', DashboardOperatorController::class);
        Route::put('/sekolah', [SekolahController::class, 'update']);

        Route::get('/siswa-impor/template', [ImporSiswaController::class, 'template']);
        Route::post('/siswa-impor/pratinjau', [ImporSiswaController::class, 'pratinjau']);
        Route::post('/siswa-impor/simpan', [ImporSiswaController::class, 'simpan']);
        Route::post('/siswa', [SiswaController::class, 'store']);
        Route::put('/siswa/{siswa}', [SiswaController::class, 'update']);
        Route::delete('/siswa/{siswa}', [SiswaController::class, 'destroy']);

        Route::post('/guru', [GuruController::class, 'store']);
        Route::put('/guru/{guru}', [GuruController::class, 'update']);

        Route::post('/kelas', [KelasController::class, 'store']);
        Route::put('/kelas/{kelas}', [KelasController::class, 'update']);
        Route::delete('/kelas/{kelas}', [KelasController::class, 'destroy']);
        Route::post('/kelas/{kelas}/anggota', [KelasController::class, 'tambahAnggota']);
        Route::post('/anggota-kelas/{anggota}/pindah', [KelasController::class, 'pindah']);
        Route::delete('/anggota-kelas/{anggota}', [KelasController::class, 'keluarkan']);

        Route::get('/orang-tua', [OrangTuaController::class, 'index']);
        Route::post('/orang-tua', [OrangTuaController::class, 'store']);
        Route::put('/orang-tua/{orangTua}', [OrangTuaController::class, 'update']);
        Route::post('/siswa/{siswa}/orang-tua', [OrangTuaController::class, 'hubungkan']);
        Route::delete('/siswa/{siswa}/orang-tua/{orangTua}', [OrangTuaController::class, 'putuskan']);
    });
});
