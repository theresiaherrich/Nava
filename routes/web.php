<?php

use App\Http\Controllers\A3_BK\Admin\AdminController;
use App\Http\Controllers\A3_BK\GuruBkController;
use App\Http\Controllers\A3_BK\SiswaController;
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
    // Dashboard (tergantung tingkat X/XI/XII)
    Route::get('/dashboard', [SiswaController::class, 'dashboard'])->name('dashboard');

    // Alur Tes RIASEC
    Route::get('/tes', [SiswaController::class, 'petunjukTes'])->name('tes.petunjuk');
    Route::get('/tes/soal', [SiswaController::class, 'soalTes'])->name('tes.soal');
    Route::post('/tes/submit', [SiswaController::class, 'submitTes'])->name('tes.submit');

    // Hasil & Rekomendasi
    Route::get('/hasil', [SiswaController::class, 'hasilTes'])->name('hasil');

    // Mapel Pilihan — kelas XI saja
    Route::get('/mapel-pilihan', [SiswaController::class, 'mapelPilihan'])->name('mapel-pilihan');

    // Riwayat Tes
    Route::get('/riwayat-tes', [SiswaController::class, 'riwayatTes'])->name('riwayat-tes');

    // Pengajuan BK
    Route::get('/pengajuan-bk', [SiswaController::class, 'pengajuanBk'])->name('pengajuan-bk');
    Route::post('/pengajuan-bk', [SiswaController::class, 'storePengajuanBk'])->name('pengajuan-bk.store');
});

// A3 PHASE 2: Route Siswa Kelas X (Integrasi dari Remote)
Route::middleware('auth')->prefix('a3/siswa/x')->name('a3.x.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\A3_BK\StudentXController::class, 'dashboard'])->name('dashboard');
    Route::get('/tes', [\App\Http\Controllers\A3_BK\StudentXController::class, 'instructions'])->name('instructions');
    Route::get('/soal', [\App\Http\Controllers\A3_BK\StudentXController::class, 'questions'])->name('questions');
    Route::post('/soal', [\App\Http\Controllers\A3_BK\StudentXController::class, 'submit'])->name('submit');
    Route::get('/hasil', [\App\Http\Controllers\A3_BK\StudentXController::class, 'latestResult'])->name('latest');
    Route::get('/hasil/{sessionId}', [\App\Http\Controllers\A3_BK\StudentXController::class, 'result'])->whereNumber('sessionId')->name('result');
    Route::get('/rekomendasi', [\App\Http\Controllers\A3_BK\StudentXController::class, 'recommendations'])->name('recommendations');
    Route::get('/riwayat', [\App\Http\Controllers\A3_BK\StudentXController::class, 'history'])->name('history');
    Route::get('/konseling', [\App\Http\Controllers\A3_BK\StudentXController::class, 'counseling'])->name('counseling');
    Route::post('/konseling', [\App\Http\Controllers\A3_BK\StudentXController::class, 'storeCounseling'])->name('counseling.store');
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
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Dimensi RIASEC
    Route::get('/dimensi', [AdminController::class, 'dimensiIndex'])->name('dimensi');
    Route::get('/dimensi/{id}/edit', [AdminController::class, 'dimensiEdit'])->name('dimensi.edit');
    Route::put('/dimensi/{id}', [AdminController::class, 'dimensiUpdate'])->name('dimensi.update');

    // Pertanyaan Tes
    Route::get('/soal', [AdminController::class, 'soalIndex'])->name('soal');
    Route::get('/soal/create', [AdminController::class, 'soalCreate'])->name('soal.create');
    Route::post('/soal', [AdminController::class, 'soalStore'])->name('soal.store');
    Route::get('/soal/{id}/edit', [AdminController::class, 'soalEdit'])->name('soal.edit');
    Route::put('/soal/{id}', [AdminController::class, 'soalUpdate'])->name('soal.update');
    Route::delete('/soal/{id}', [AdminController::class, 'soalDestroy'])->name('soal.destroy');
    Route::patch('/soal/{id}/toggle', [AdminController::class, 'soalToggle'])->name('soal.toggle');

    // Periode Tes
    Route::get('/periode', [AdminController::class, 'periodeIndex'])->name('periode');
    Route::get('/periode/create', [AdminController::class, 'periodeCreate'])->name('periode.create');
    Route::post('/periode', [AdminController::class, 'periodeStore'])->name('periode.store');
    Route::get('/periode/{id}/edit', [AdminController::class, 'periodeEdit'])->name('periode.edit');
    Route::put('/periode/{id}', [AdminController::class, 'periodeUpdate'])->name('periode.update');
    Route::delete('/periode/{id}', [AdminController::class, 'periodeDestroy'])->name('periode.destroy');

    // Mapel Pilihan
    Route::get('/mapel', [AdminController::class, 'mapelIndex'])->name('mapel');
    Route::get('/mapel/create', [AdminController::class, 'mapelCreate'])->name('mapel.create');
    Route::post('/mapel', [AdminController::class, 'mapelStore'])->name('mapel.store');
    Route::get('/mapel/{id}/edit', [AdminController::class, 'mapelEdit'])->name('mapel.edit');
    Route::put('/mapel/{id}', [AdminController::class, 'mapelUpdate'])->name('mapel.update');
    Route::delete('/mapel/{id}', [AdminController::class, 'mapelDestroy'])->name('admin.mapel.destroy');

    // Program Studi
    Route::get('/prodi', [AdminController::class, 'prodiIndex'])->name('prodi');
    Route::get('/prodi/create', [AdminController::class, 'prodiCreate'])->name('prodi.create');
    Route::post('/prodi', [AdminController::class, 'prodiStore'])->name('prodi.store');
    Route::get('/prodi/{id}/edit', [AdminController::class, 'prodiEdit'])->name('prodi.edit');
    Route::put('/prodi/{id}', [AdminController::class, 'prodiUpdate'])->name('prodi.update');
    Route::delete('/prodi/{id}', [AdminController::class, 'prodiDestroy'])->name('prodi.destroy');

    // Profesi
    Route::get('/profesi', [AdminController::class, 'profesiIndex'])->name('profesi');
    Route::get('/profesi/create', [AdminController::class, 'profesiCreate'])->name('profesi.create');
    Route::post('/profesi', [AdminController::class, 'profesiStore'])->name('profesi.store');
    Route::get('/profesi/{id}/edit', [AdminController::class, 'profesiEdit'])->name('profesi.edit');
    Route::put('/profesi/{id}', [AdminController::class, 'profesiUpdate'])->name('profesi.update');
    Route::delete('/profesi/{id}', [AdminController::class, 'profesiDestroy'])->name('profesi.destroy');

    // Validasi Profil
    Route::get('/validasi', [AdminController::class, 'validasiIndex'])->name('validasi');
    Route::put('/validasi/mapel/{id}', [AdminController::class, 'validasiMapel'])->name('validasi.mapel');
    Route::put('/validasi/prodi/{id}', [AdminController::class, 'validasiProdi'])->name('validasi.prodi');
});