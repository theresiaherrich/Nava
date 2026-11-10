<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InputNilaiController;
use App\Http\Controllers\VerifikasiNilaiController;
use App\Http\Controllers\RekapNilaiController;
use App\Http\Controllers\PeriodePenguncianController;
use App\Http\Controllers\PelengkapRaporController;
use App\Http\Controllers\CetakRaporController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\RaporSayaController;
use App\Http\Controllers\NilaiPerMapelController;
use App\Http\Controllers\RiwayatRaporController;
use App\Http\Controllers\BerandaAkademikController;
use App\Http\Controllers\DashboardKurikulumController;
use App\Http\Controllers\DashboardOperatorController;
use App\Http\Controllers\AkunPeranController;
use App\Http\Controllers\DataSiswaController;
use App\Http\Controllers\DataGuruController;
use App\Http\Controllers\DataKelasController;
use App\Http\Controllers\RelasiOrangTuaController;

Route::get('/relasi-orang-tua', [RelasiOrangTuaController::class, 'index'])
    ->name('relasi-orang-tua.index');

Route::post('/relasi-orang-tua', [RelasiOrangTuaController::class, 'store'])
    ->name('relasi-orang-tua.store');

Route::put('/relasi-orang-tua/{ortu}', [RelasiOrangTuaController::class, 'update'])
    ->name('relasi-orang-tua.update');

Route::delete('/relasi-orang-tua/{ortu}', [RelasiOrangTuaController::class, 'destroy'])
    ->name('relasi-orang-tua.destroy');

Route::get('/data-kelas', [DataKelasController::class, 'index'])
    ->name('data-kelas.index');

Route::post('/data-kelas/kelas', [DataKelasController::class, 'tambahKelas'])
    ->name('data-kelas.kelas.store');

Route::post('/data-kelas/pindah', [DataKelasController::class, 'pindah'])
    ->name('data-kelas.pindah');

Route::post('/data-kelas/{kelas}/siswa', [DataKelasController::class, 'tambahSiswa'])
    ->name('data-kelas.siswa.store');

Route::get('/data-siswa/format', [DataSiswaController::class, 'format'])->name('data-siswa.format');
Route::post('/data-siswa/impor', [DataSiswaController::class, 'impor'])->name('data-siswa.impor');
Route::post('/data-siswa', [DataSiswaController::class, 'store'])->name('data-siswa.store');

Route::get('/data-guru/format', [DataGuruController::class, 'format'])->name('data-guru.format');
Route::post('/data-guru/impor', [DataGuruController::class, 'impor'])->name('data-guru.impor');
Route::post('/data-guru', [DataGuruController::class, 'store'])->name('data-guru.store');

Route::get('/data-guru', [DataGuruController::class, 'index'])
    ->name('data-guru.index');

Route::get('/data-siswa', [DataSiswaController::class, 'index'])
    ->name('data-siswa.index');

Route::get('/akun-peran', [AkunPeranController::class, 'index'])
    ->name('akun-peran.index');

Route::get('/dashboard-kurikulum', [DashboardKurikulumController::class, 'index'])
    ->name('dashboard-kurikulum.index');

Route::get('/dashboard-operator', [DashboardOperatorController::class, 'index'])
    ->name('dashboard-operator.index');

Route::get('/beranda-akademik', [BerandaAkademikController::class, 'index'])
    ->name('beranda-akademik.index');

Route::get('/riwayat-rapor', [RiwayatRaporController::class, 'index'])
    ->name('riwayat-rapor.index');
Route::get('/', function () {
    return view('welcome');
});

Route::view('/test-sidebar', 'test-sidebar');

// A1 - PRESENSI
Route::prefix('presensi')->name('presensi.')->group(function () {
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
});

Route::get('/rapor-saya', [RaporSayaController::class, 'index'])
    ->name('rapor-saya.index');
 
Route::get('/nilai-per-mapel', [NilaiPerMapelController::class, 'index'])
    ->name('nilai-per-mapel.index');

Route::get('/nilai-per-mapel/{id}', [NilaiPerMapelController::class, 'show'])
    ->name('nilai-per-mapel.show');

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