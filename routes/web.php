<?php

use App\Http\Controllers\A3_BK\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\A3_BK\Admin\DimensiController;
use App\Http\Controllers\A3_BK\Admin\MapelPilihanController;
use App\Http\Controllers\A3_BK\Admin\PeriodeTesController;
use App\Http\Controllers\A3_BK\Admin\ProfesiController;
use App\Http\Controllers\A3_BK\Admin\ProgramStudiController;
use App\Http\Controllers\A3_BK\Admin\SoalController;
use App\Http\Controllers\A3_BK\Admin\ValidasiController;
use App\Http\Controllers\A3_BK\GuruBkController;
use App\Http\Controllers\A3_BK\Siswa\DashboardController as SiswaDashboardController;
use App\Http\Controllers\A3_BK\Siswa\HasilController;
use App\Http\Controllers\A3_BK\Siswa\PengajuanController;
use App\Http\Controllers\A3_BK\Siswa\RiwayatController;
use App\Http\Controllers\A3_BK\Siswa\TesController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// ==========================================
// 1. AUTENTIKASI
// ==========================================
Route::get('/', [LoginController::class, 'showLoginForm']);
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ==========================================
// 2. AREA SISWA (role: SISWA)
// ==========================================
Route::middleware(['auth', 'role:SISWA'])->prefix('siswa')->name('siswa.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');

    // Alur Tes RIASEC
    Route::get('/tes', [TesController::class, 'petunjuk'])->name('tes.petunjuk');
    Route::get('/tes/soal', [TesController::class, 'soal'])->name('tes.soal');
    Route::post('/tes/submit', [TesController::class, 'submit'])->name('tes.submit');

    // Hasil & Rekomendasi
    Route::get('/hasil', [HasilController::class, 'index'])->name('hasil');

    // Mapel Pilihan — kelas XI saja
    Route::get('/mapel-pilihan', [HasilController::class, 'mapelPilihan'])->name('mapel-pilihan');

    // Riwayat Tes
    Route::get('/riwayat-tes', [RiwayatController::class, 'index'])->name('riwayat-tes');

    // Pengajuan BK
    Route::get('/pengajuan-bk', [PengajuanController::class, 'index'])->name('pengajuan-bk');
    Route::post('/pengajuan-bk', [PengajuanController::class, 'store'])->name('pengajuan-bk.store');
});

// ==========================================
// 3. AREA GURU BK (role: GURU + ada di guru_bk)
// ==========================================
Route::middleware(['auth', 'role:GURU'])->prefix('bk')->name('bk.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [GuruBkController::class, 'dashboard'])->name('dashboard');

    // Rekap Tes RIASEC
    Route::get('/rekap-tes', [GuruBkController::class, 'rekapTes'])->name('rekap-tes');
    Route::get('/rekap-tes/{id}', [GuruBkController::class, 'detailTes'])->name('rekap-tes.detail');

    // Pengajuan BK
    Route::get('/pengajuan', [GuruBkController::class, 'pengajuanBk'])->name('pengajuan');
    Route::get('/pengajuan/{id}/tindak-lanjut', [GuruBkController::class, 'tindakLanjut'])->name('pengajuan.tindak-lanjut');
    Route::put('/pengajuan/{id}', [GuruBkController::class, 'updatePengajuan'])->name('pengajuan.update');

    // Riwayat Bimbingan
    Route::get('/riwayat', [GuruBkController::class, 'riwayatBimbingan'])->name('riwayat');

    // Profil Guru BK
    Route::get('/profil', [GuruBkController::class, 'profil'])->name('profil');
    Route::put('/profil', [GuruBkController::class, 'updateProfil'])->name('profil.update');
    Route::put('/profil/password', [GuruBkController::class, 'ubahPassword'])->name('profil.password');
});

// ==========================================
// 4. AREA ADMINISTRATOR (role: ADMIN)
// ==========================================
Route::middleware(['auth', 'role:ADMIN'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Dimensi RIASEC
    Route::get('/dimensi', [DimensiController::class, 'index'])->name('dimensi');
    Route::get('/dimensi/{id}/edit', [DimensiController::class, 'edit'])->name('dimensi.edit');
    Route::put('/dimensi/{id}', [DimensiController::class, 'update'])->name('dimensi.update');

    // Pertanyaan Tes
    Route::get('/soal', [SoalController::class, 'index'])->name('soal');
    Route::get('/soal/create', [SoalController::class, 'create'])->name('soal.create');
    Route::post('/soal', [SoalController::class, 'store'])->name('soal.store');
    Route::get('/soal/{id}/edit', [SoalController::class, 'edit'])->name('soal.edit');
    Route::put('/soal/{id}', [SoalController::class, 'update'])->name('soal.update');
    Route::delete('/soal/{id}', [SoalController::class, 'destroy'])->name('soal.destroy');
    Route::patch('/soal/{id}/toggle', [SoalController::class, 'toggle'])->name('soal.toggle');

    // Periode Tes
    Route::get('/periode', [PeriodeTesController::class, 'index'])->name('periode');
    Route::get('/periode/create', [PeriodeTesController::class, 'create'])->name('periode.create');
    Route::post('/periode', [PeriodeTesController::class, 'store'])->name('periode.store');
    Route::get('/periode/{id}/edit', [PeriodeTesController::class, 'edit'])->name('periode.edit');
    Route::put('/periode/{id}', [PeriodeTesController::class, 'update'])->name('periode.update');
    Route::delete('/periode/{id}', [PeriodeTesController::class, 'destroy'])->name('periode.destroy');

    // Mapel Pilihan
    Route::get('/mapel', [MapelPilihanController::class, 'index'])->name('mapel');
    Route::get('/mapel/create', [MapelPilihanController::class, 'create'])->name('mapel.create');
    Route::post('/mapel', [MapelPilihanController::class, 'store'])->name('mapel.store');
    Route::get('/mapel/{id}/edit', [MapelPilihanController::class, 'edit'])->name('mapel.edit');
    Route::put('/mapel/{id}', [MapelPilihanController::class, 'update'])->name('mapel.update');
    Route::delete('/mapel/{id}', [MapelPilihanController::class, 'destroy'])->name('admin.mapel.destroy');

    // Program Studi
    Route::get('/prodi', [ProgramStudiController::class, 'index'])->name('prodi');
    Route::get('/prodi/create', [ProgramStudiController::class, 'create'])->name('prodi.create');
    Route::post('/prodi', [ProgramStudiController::class, 'store'])->name('prodi.store');
    Route::get('/prodi/{id}/edit', [ProgramStudiController::class, 'edit'])->name('prodi.edit');
    Route::put('/prodi/{id}', [ProgramStudiController::class, 'update'])->name('prodi.update');
    Route::delete('/prodi/{id}', [ProgramStudiController::class, 'destroy'])->name('prodi.destroy');

    // Profesi
    Route::get('/profesi', [ProfesiController::class, 'index'])->name('profesi');
    Route::get('/profesi/create', [ProfesiController::class, 'create'])->name('profesi.create');
    Route::post('/profesi', [ProfesiController::class, 'store'])->name('profesi.store');
    Route::get('/profesi/{id}/edit', [ProfesiController::class, 'edit'])->name('profesi.edit');
    Route::put('/profesi/{id}', [ProfesiController::class, 'update'])->name('profesi.update');
    Route::delete('/profesi/{id}', [ProfesiController::class, 'destroy'])->name('profesi.destroy');

    // Validasi Profil
    Route::get('/validasi', [ValidasiController::class, 'index'])->name('validasi');
    Route::put('/validasi/mapel/{id}', [ValidasiController::class, 'validasiMapel'])->name('validasi.mapel');
    Route::put('/validasi/prodi/{id}', [ValidasiController::class, 'validasiProdi'])->name('validasi.prodi');
});