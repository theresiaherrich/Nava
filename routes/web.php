<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InputNilaiController;
use App\Http\Controllers\VerifikasiNilaiController;
use App\Http\Controllers\RekapNilaiController;
use App\Http\Controllers\PeriodePenguncianController;
use App\Http\Controllers\PelengkapRaporController;
use App\Http\Controllers\CetakRaporController;
use App\Http\Controllers\PortalDashboardController;
use App\Http\Controllers\ProfilSiswaController;
use App\Http\Controllers\RekapPresensiController;
use App\Http\Controllers\AkademikNilaiController;
use App\Http\Controllers\MinatBakatController;
use App\Http\Controllers\Auth\OrangTuaAuthController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ManajemenPenggunaController;
use App\Http\Controllers\Admin\RelasiOrangTuaController;
use App\Http\Controllers\Admin\NotifikasiController;
use App\Http\Controllers\Admin\PengaturanAdminController;
use App\Http\Middleware\EnsureOrangTua;
use App\Http\Middleware\EnsureOrangTuaVerified;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\DB;

Route::get('/test-database', function () {
    try {
        $database = DB::select('SELECT DATABASE() AS nama');

        $jumlahSiswa = DB::table('siswa')->count();

        return response()->json([
            'status' => 'Berhasil terhubung!',
            'database' => $database[0]->nama,
            'jumlah_siswa' => $jumlahSiswa,
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'Gagal terhubung',
            'error' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/', function () {
    return view('welcome');
});

Route::view('/test-sidebar', 'test-sidebar');

// // Halaman Input Nilai tanpa login
// Route::get('/input-nilai', [InputNilaiController::class, 'index'])
//     ->name('input-nilai.index');

// // Halaman Verifikasi Nilai tanpa login
// Route::prefix('verifikasi-nilai')
//     ->name('verifikasi-nilai.')
//     ->group(function () {
//         Route::get('/', [VerifikasiNilaiController::class, 'index'])->name('index');
//         Route::get('/{mapel}', [VerifikasiNilaiController::class, 'show'])->name('show');
//         Route::post('/{mapel}/revisi', [VerifikasiNilaiController::class, 'requestRevision'])->name('revisi');
//         Route::post('/{mapel}/setujui', [VerifikasiNilaiController::class, 'approve'])->name('setujui');
//     });

// // Halaman Rekap Nilai
// Route::get('/rekap-nilai', [RekapNilaiController::class, 'index'])
//     ->name('rekap-nilai.index');

// // Halaman Periode & Penguncian
// Route::get('/periode-penguncian', [PeriodePenguncianController::class, 'index'])
//     ->name('periode-penguncian.index');

// Route::prefix('pelengkap-rapor')
//     ->name('pelengkap-rapor.')
//     ->group(function () {
//         Route::get('/', [PelengkapRaporController::class, 'index'])->name('index');
//         Route::post('/simpan', [PelengkapRaporController::class, 'simpan'])->name('simpan');
//         Route::post('/siswa/{siswa}/catatan', [PelengkapRaporController::class, 'simpanCatatan'])->name('catatan');
//     });

// // Halaman Cetak & Ekspor Rapor
// Route::prefix('cetak-rapor')
//     ->name('cetak-rapor.')
//     ->group(function () {
//         Route::get('/', [CetakRaporController::class, 'index'])->name('index');
//         Route::post('/kelas/unduh', [CetakRaporController::class, 'unduhKelas'])->name('kelas');
//         Route::post('/siswa/{siswa}/unduh', [CetakRaporController::class, 'unduhSiswa'])->name('siswa');
//         Route::get('/riwayat/{riwayat}/unduh', [CetakRaporController::class, 'unduhUlang'])->name('ulang');
//     });

// ==================================================
// AUTH ORANG TUA
// ==================================================

Route::prefix('orang-tua')
    ->name('ortu.')
    ->group(function () {

        // Halaman publik
        Route::middleware('guest')->group(function () {
            Route::get('/login', [
                OrangTuaAuthController::class,
                'showLogin'
            ])->name('login');

            Route::post('/login', [
                OrangTuaAuthController::class,
                'login'
            ])->name('login.submit');

            Route::get('/register', [
                OrangTuaAuthController::class,
                'showRegister'
            ])->name('register');

            Route::post('/register', [
                OrangTuaAuthController::class,
                'register'
            ])->name('register.submit');
        });

        // Halaman yang memerlukan login
        Route::middleware(EnsureOrangTua::class)->group(function () {

            Route::get('/status-verifikasi', [
                OrangTuaAuthController::class,
                'status'
            ])->name('status');

            Route::post('/logout', [
                OrangTuaAuthController::class,
                'logout'
            ])->name('logout');
        });
    });


// ==================================================
// PORTAL ORANG TUA - WAJIB LOGIN DAN VERIFIED
// ==================================================

Route::prefix('portal-orang-tua')
    ->name('portal.')
    ->middleware([
        EnsureOrangTua::class,
        EnsureOrangTuaVerified::class,
    ])
    ->group(function () {

        Route::get('/', [
            PortalDashboardController::class,
            'index'
        ])->name('dashboard');

        Route::get('/profil-siswa', [
            ProfilSiswaController::class,
            'index'
        ])->name('profil');

        Route::get('/rekap-presensi', [
            RekapPresensiController::class,
            'index'
        ])->name('presensi');

        Route::get('/akademik-nilai', [
            AkademikNilaiController::class,
            'index'
        ])->name('akademik');

        Route::get('/minat-bakat-karir', [
            MinatBakatController::class,
            'index'
        ])->name('minat-bakat');

        Route::get('/rapor/unduh', [
            AkademikNilaiController::class,
            'unduh'
        ])->name('rapor.unduh');

        Route::get('/presensi/unduh', [
            RekapPresensiController::class,
            'unduh'
        ])->name('presensi.unduh');
    });


Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware(EnsureAdmin::class)->group(function () {
        // 1. Dashboard Admin
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // 2. Manajemen Pengguna
        Route::get('/pengguna', [ManajemenPenggunaController::class, 'index'])->name('users.index');
        Route::get('/pengguna/export', [ManajemenPenggunaController::class, 'export'])->name('users.export');
        Route::post('/pengguna', [ManajemenPenggunaController::class, 'store'])->name('users.store');
        Route::put('/pengguna/{id}/username', [ManajemenPenggunaController::class, 'updateUsername'])->name('users.username');
        Route::put('/pengguna/{id}/password', [ManajemenPenggunaController::class, 'resetPassword'])->name('users.password');

        // 3. Relasi Orang Tua
        Route::get('/relasi', [RelasiOrangTuaController::class, 'index'])->name('relations.index');
        Route::post('/relasi', [RelasiOrangTuaController::class, 'store'])->name('relations.store');
        Route::put('/relasi/{idSiswa}/{hubungan}/verifikasi', [RelasiOrangTuaController::class, 'verify'])->name('relations.verify');
        Route::delete('/relasi/{idSiswa}/{hubungan}', [RelasiOrangTuaController::class, 'destroy'])->name('relations.destroy');

        // 4. Notifikasi WhatsApp
        Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifications.index');
        Route::post('/notifikasi/kirim-sekarang', [NotifikasiController::class, 'kirimSekarang'])->name('notifications.send');
        Route::post('/notifikasi/{id}/ulang', [NotifikasiController::class, 'ulang'])->name('notifications.retry');
        Route::post('/notifications/rekap', [\App\Http\Controllers\Admin\NotifikasiController::class, 'kirimRekap'])->name('notifications.rekap');

        // 5. Pengaturan Admin
        Route::get('/pengaturan', [PengaturanAdminController::class, 'index'])->name('settings.index');
        Route::put('/pengaturan/password', [PengaturanAdminController::class, 'updatePassword'])->name('settings.password');
    });
});

// Placeholder, nanti diganti dengan controller sungguhan
Route::get('/rekap-nilai/export/{scope}', function ($scope) {
    return 'Export ' . $scope . ' belum dibuat';
})->name('rekap-nilai.export');

Route::get('/rekap-nilai/rombel/{rombel}', function ($rombel) {
    return 'Detail rombel ' . $rombel . ' belum dibuat';
})->name('rekap-nilai.show');

Route::get('/kelola-tp', function () {
    return 'Halaman Kelola TP belum dibuat';
})->name('tp.index');