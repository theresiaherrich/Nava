<?php

use App\Http\Controllers\Web\DashboardWaliKelasController;
use App\Http\Controllers\Web\GuruPageController;
use App\Support\WebNavigation;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));
Route::get('/login', fn () => auth()->check() ? redirect()->route('dashboard') : view('auth.login'))->name('login');

Route::middleware(['auth', 'account.active'])->group(function () {
    Route::view('/ganti-sandi', 'auth.change-password')->name('password.change');

    Route::middleware('password.changed')->group(function () {
        Route::get('/dashboard', fn () => redirect()->to(WebNavigation::dashboard()))->name('dashboard');

        Route::middleware('role:GURU')->prefix('guru')->group(function () {
            Route::view('/dashboard', 'academic.dashboard', ['title' => 'Dashboard Guru', 'endpoint' => '/guru/dashboard']);
            Route::get('/tp', [GuruPageController::class, 'tp'])->name('tp.index');
            Route::get('/input-nilai', [GuruPageController::class, 'nilai'])->name('input-nilai.index');
        });

        Route::middleware('role:WALI_KELAS')->prefix('wali-kelas')->group(function () {
            Route::get('/dashboard', DashboardWaliKelasController::class);
            Route::view('/verifikasi-nilai', 'academic.penilaian', ['mode' => 'verifikasi', 'title' => 'Verifikasi Nilai']);
            Route::view('/cetak-rapor', 'academic.rapor', ['prefix' => 'wali', 'title' => 'Cetak dan Arsip Rapor']);
            Route::view('/rekap-nilai', 'academic.rapor', ['prefix' => 'wali', 'title' => 'Rekap Nilai', 'dkn' => true]);
        });

        Route::middleware('role:KURIKULUM')->prefix('kurikulum')->group(function () {
            Route::view('/dashboard', 'academic.dashboard', ['title' => 'Dashboard Kurikulum', 'endpoint' => '/kurikulum/dashboard']);
            Route::view('/periode-penguncian', 'academic.penilaian', ['mode' => 'periode', 'title' => 'Periode dan Penguncian']);
            Route::view('/cetak-rapor', 'academic.rapor', ['prefix' => 'kurikulum', 'title' => 'Cetak, Terbitkan dan Arsip Rapor']);
            Route::view('/rekap-nilai', 'academic.rapor', ['prefix' => 'kurikulum', 'title' => 'Rekap Nilai', 'dkn' => true]);
            Route::view('/data-akademik', 'academic.master', ['title' => 'Data Akademik', 'kurikulum' => true]);
        });

        Route::middleware('role:ADMIN')->prefix('operator')->group(function () {
            Route::view('/dashboard', 'academic.dashboard', ['title' => 'Dashboard Operator', 'endpoint' => '/master/dashboard']);
            Route::view('/data-master', 'academic.master', ['title' => 'Data Master']);
        });
        Route::view('/superadmin/dashboard', 'academic.dashboard', ['title' => 'Dashboard Super Admin'])->middleware('role:SUPERADMIN');
        Route::view('/kesiswaan/dashboard', 'academic.dashboard', ['title' => 'Dashboard Kesiswaan', 'endpoint' => '/kesiswaan/dashboard'])->middleware('role:KESISWAAN');
        Route::view('/kesiswaan/ekskul', 'academic.ekskul')->middleware('role:KESISWAAN');
        Route::view('/bk/dashboard', 'academic.dashboard', ['title' => 'Dashboard Guru BK'])->middleware('role:BUDAYA_POSITIF');

        foreach (['siswa' => 'SISWA', 'orang-tua' => 'ORANG_TUA'] as $prefix => $role) {
            Route::prefix($prefix)->middleware('role:'.$role)->group(function () use ($prefix) {
                Route::view('/beranda', 'academic.portal', ['prefix' => $prefix, 'mode' => 'beranda']);
                Route::view('/nilai', 'academic.portal', ['prefix' => $prefix, 'mode' => 'nilai']);
                Route::view('/nilai/{rombel}', 'academic.portal', ['prefix' => $prefix, 'mode' => 'detail'])->whereNumber('rombel');
                Route::view('/rapor', 'academic.portal', ['prefix' => $prefix, 'mode' => 'rapor']);
            });
        }
    });
});
