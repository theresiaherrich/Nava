<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\CsrfController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Master\GuruController;
use App\Http\Controllers\Master\KelasController;
use App\Http\Controllers\Master\MapelController;
use App\Http\Controllers\Master\SekolahController;
use App\Http\Controllers\Master\SiswaController;
use App\Http\Controllers\Master\TahunAjaranController;
use App\Http\Controllers\Master\WaliKelasController;
use App\Http\Controllers\Penilaian\PeriodeController;
use Illuminate\Support\Facades\Route;

// Dimuat di bawah prefix /api dengan middleware "web" (sesi + CSRF).

Route::get('/csrf-token', CsrfController::class);

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/password/forgot', [PasswordController::class, 'forgot'])->middleware('throttle:5,1');
    Route::post('/password/reset', [PasswordController::class, 'reset'])->middleware('throttle:10,1');

    Route::middleware('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/switch-role', [AuthController::class, 'switchRole']);
        Route::post('/password/change', [PasswordController::class, 'change']);
    });
});

Route::middleware(['auth', 'account.active'])->group(function () {

    // Operator: akun, peran, audit
    Route::middleware('role:ADMIN')->prefix('admin')->group(function () {
        Route::get('/accounts', [AccountController::class, 'index']);
        Route::post('/accounts', [AccountController::class, 'store']);
        Route::put('/accounts/{user}', [AccountController::class, 'update']);
        Route::put('/accounts/{user}/roles', [AccountController::class, 'setRoles']);
        Route::post('/accounts/{user}/setujui', [AccountController::class, 'setujui']);
        Route::patch('/accounts/{user}/status', [AccountController::class, 'setStatus']);
        Route::post('/accounts/{user}/reset-password', [AccountController::class, 'resetPassword']);
        Route::post('/accounts/{user}/profil', [AccountController::class, 'linkProfil']);
        Route::get('/audit-log', [AuditLogController::class, 'index']);
    });

    Route::prefix('master')->group(function () {

        // Tahun ajaran & mapel: dibaca Operator maupun Kurikulum
        Route::middleware('role:ADMIN,KURIKULUM')->group(function () {
            Route::get('/tahun-ajaran', [TahunAjaranController::class, 'index']);
            Route::get('/mapel', [MapelController::class, 'index']);
        });

        // Kurikulum: tahun ajaran, mapel, penetapan wali kelas
        Route::middleware('role:KURIKULUM')->group(function () {
            Route::post('/tahun-ajaran', [TahunAjaranController::class, 'store']);
            Route::post('/tahun-ajaran/{tahunAjaran}/aktifkan', [TahunAjaranController::class, 'aktifkan']);
            Route::post('/semester/{semester}/aktifkan', [TahunAjaranController::class, 'aktifkanSemester']);
            Route::post('/mapel', [MapelController::class, 'store']);
            Route::put('/mapel/{mapel}', [MapelController::class, 'update']);
            Route::post('/kelas/{kelas}/wali', [WaliKelasController::class, 'store']);
            Route::delete('/wali-kelas/{waliKelas}', [WaliKelasController::class, 'destroy']);
        });

        // Operator: data siswa, guru, kelas, penempatan, sekolah
        Route::middleware('role:ADMIN')->group(function () {
            Route::get('/siswa', [SiswaController::class, 'index']);
            Route::post('/siswa', [SiswaController::class, 'store']);
            Route::put('/siswa/{siswa}', [SiswaController::class, 'update']);
            Route::post('/siswa/{siswa}/orang-tua', [SiswaController::class, 'hubungkanOrangTua']);
            Route::post('/siswa/{siswa}/pindah-kelas', [KelasController::class, 'pindahSiswa']);

            Route::get('/guru', [GuruController::class, 'index']);
            Route::post('/guru', [GuruController::class, 'store']);
            Route::put('/guru/{guru}', [GuruController::class, 'update']);

            Route::get('/kelas', [KelasController::class, 'index']);
            Route::post('/kelas', [KelasController::class, 'store']);
            Route::put('/kelas/{kelas}', [KelasController::class, 'update']);
            Route::get('/kelas/{kelas}/anggota', [KelasController::class, 'anggota']);
            Route::post('/kelas/{kelas}/anggota', [KelasController::class, 'tambahSiswa']);

            Route::get('/sekolah', [SekolahController::class, 'show']);
            Route::put('/sekolah', [SekolahController::class, 'update']);
        });
    });

    // Kurikulum: periode, penguncian, koreksi nilai
    Route::prefix('penilaian')->middleware('role:KURIKULUM')->group(function () {
        Route::get('/periode', [PeriodeController::class, 'index']);
        Route::post('/periode', [PeriodeController::class, 'store']);
        Route::get('/periode/{periode}/ringkasan', [PeriodeController::class, 'ringkasan']);
        Route::patch('/periode/{periode}/jadwal', [PeriodeController::class, 'ubahJadwal']);
        Route::patch('/periode/{periode}/status', [PeriodeController::class, 'ubahStatus']);
        Route::post('/periode/{periode}/kelas/{kelas}/kunci', [PeriodeController::class, 'kunciKelas']);
        Route::post('/periode/{periode}/kunci-semua-siap', [PeriodeController::class, 'kunciSemuaSiap']);
        Route::post('/paket/{paket}/buka-koreksi', [PeriodeController::class, 'bukaKoreksi']);
    });
});
