<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InputNilaiController;
use App\Http\Controllers\VerifikasiNilaiController;
use App\Http\Controllers\RekapNilaiController;
use App\Http\Controllers\PeriodePenguncianController;
use App\Http\Controllers\PelengkapRaporController;
use App\Http\Controllers\CetakRaporController;
use App\Http\Controllers\PresensiController;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/test-sidebar', 'test-sidebar');

// Fe - Halaman Login & Register
Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');

// A1 - PRESENSI
Route::prefix('presensi')->name('presensi.')->group(function () {

    // Fitur umum presensi
    Route::view('/qr-code', 'presensi.qr-code.index')
        ->name('qr-code');

    Route::view('/manual', 'presensi.manual.index')
        ->name('manual');

    Route::view('/riwayat', 'presensi.riwayat.index')
        ->name('riwayat');

    Route::get('/kelola', [PresensiController::class, 'kelola'])
        ->name('kelola');

    Route::view('/scan', 'presensi.scan.index')
        ->name('scan');

    Route::view('/edit', 'presensi.edit.index')
        ->name('edit');


    // ==========================================
    // ROLE SISWA
    // ==========================================

    Route::view('/siswa', 'presensi.siswa.index')
        ->name('siswa');


    // ==========================================
    // ROLE GURU / WALI KELAS
    // ==========================================

    Route::view('/guru', 'presensi.guru.index', [
        'pageTitle' => 'Kelola Presensi',
        'rolePresensi' => 'guru',
        'namaProfil' => 'Nama Guru',
        'labelProfil' => 'Guru Wali Kelas',
        'detailProfil' => 'XI MIPA 2',
    ])->name('guru');


    // ==========================================
    // ROLE KURIKULUM
    // Menggunakan dashboard yang sama dengan Guru
    // ==========================================

    Route::view('/kurikulum', 'presensi.guru.index', [
        'pageTitle' => 'Dashboard',
        'rolePresensi' => 'kurikulum',
        'namaProfil' => 'Kurikulum',
        'labelProfil' => 'Kurikulum',
        'detailProfil' => 'SMAN 1 Malang',
    ])->name('kurikulum');


    // ==========================================
    // ROLE ADMIN
    // ==========================================

    Route::view('/admin', 'presensi.admin.index')
        ->name('admin');


    // ==========================================
    // ROLE BUDAYA POSITIF
    // Dashboard menggunakan template Guru
    // ==========================================

    Route::view('/budaya-positif', 'presensi.guru.index', [
        'pageTitle' => 'Dashboard',
        'rolePresensi' => 'budaya-positif',
        'namaProfil' => 'Budaya Positif',
        'labelProfil' => 'Budaya Positif',
        'detailProfil' => 'SMAN 1 Malang',
    ])->name('budaya-positif');


    // ==========================================
    // ADMIN PRESENSI BUDAYA POSITIF
    // Tetap menggunakan halaman Admin Presensi
    // ==========================================

    Route::view('/budaya-positif/admin-presensi', 'presensi.admin.index')
        ->name('budaya-positif.admin-presensi');

});

// Halaman Input Nilai tanpa login
Route::get('/input-nilai', [InputNilaiController::class, 'index'])
    ->name('input-nilai.index');

// Halaman Verifikasi Nilai tanpa login
Route::prefix('verifikasi-nilai')
    ->name('verifikasi-nilai.')
    ->group(function () {
        Route::get('/', [VerifikasiNilaiController::class, 'index'])->name('index');
        Route::get('/{mapel}', [VerifikasiNilaiController::class, 'show'])->name('show');
        Route::post('/{mapel}/revisi', [VerifikasiNilaiController::class, 'requestRevision'])->name('revisi');
        Route::post('/{mapel}/setujui', [VerifikasiNilaiController::class, 'approve'])->name('setujui');
    });

// Halaman Rekap Nilai
Route::get('/rekap-nilai', [RekapNilaiController::class, 'index'])
    ->name('rekap-nilai.index');

// Halaman Periode & Penguncian
Route::get('/periode-penguncian', [PeriodePenguncianController::class, 'index'])
    ->name('periode-penguncian.index');

Route::prefix('pelengkap-rapor')
    ->name('pelengkap-rapor.')
    ->group(function () {
        Route::get('/', [PelengkapRaporController::class, 'index'])->name('index');
        Route::post('/simpan', [PelengkapRaporController::class, 'simpan'])->name('simpan');
        Route::post('/siswa/{siswa}/catatan', [PelengkapRaporController::class, 'simpanCatatan'])->name('catatan');
    });

// Halaman Cetak & Ekspor Rapor
Route::prefix('cetak-rapor')
    ->name('cetak-rapor.')
    ->group(function () {
        Route::get('/', [CetakRaporController::class, 'index'])->name('index');
        Route::post('/kelas/unduh', [CetakRaporController::class, 'unduhKelas'])->name('kelas');
        Route::post('/siswa/{siswa}/unduh', [CetakRaporController::class, 'unduhSiswa'])->name('siswa');
        Route::get('/riwayat/{riwayat}/unduh', [CetakRaporController::class, 'unduhUlang'])->name('ulang');
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