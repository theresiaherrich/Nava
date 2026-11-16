<?php

use App\Http\Controllers\Guru\NilaiController;
use App\Http\Controllers\Guru\PaketController;
use App\Http\Controllers\Guru\RombelGuruController;
use App\Http\Controllers\Guru\TpController;
use App\Http\Controllers\Kurikulum\PeriodeController;
use App\Http\Controllers\Wali\VerifikasiController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------- Kurikulum: periode & penguncian
Route::prefix('kurikulum')->middleware('role:KURIKULUM')->group(function () {
    Route::get('/periode', [PeriodeController::class, 'index']);
    Route::put('/periode/{periode}', [PeriodeController::class, 'atur']);
    Route::post('/periode/{periode}/tutup', [PeriodeController::class, 'tutup']);
    Route::get('/periode/{periode}/ringkasan', [PeriodeController::class, 'ringkasan']);
    Route::post('/periode/{periode}/kelas/{kelas}/kunci', [PeriodeController::class, 'kunciKelas']);
    Route::post('/periode/{periode}/kunci-semua-siap', [PeriodeController::class, 'kunciSemuaSiap']);
    Route::post('/paket/{paket}/buka-koreksi', [PeriodeController::class, 'bukaKoreksi'])->name('kurikulum.paket.koreksi');
    Route::put('/tp/{tp}/kktp', [PeriodeController::class, 'aturKktp']);
    Route::get('/rombel/{rombel}/rekap', [NilaiController::class, 'rekap']);
});

// ---------------------------------------------------------------- Guru mapel (hanya rombel yang diampu)
Route::prefix('guru')->middleware('role:GURU')->group(function () {
    Route::get('/dashboard', [RombelGuruController::class, 'dashboard']);
    Route::get('/periode', [PeriodeController::class, 'index']);
    Route::get('/rombel', [RombelGuruController::class, 'index']);
    Route::get('/rombel/{rombel}', [RombelGuruController::class, 'show']);

    Route::get('/rombel/{rombel}/tp', [TpController::class, 'index']);
    Route::post('/rombel/{rombel}/tp', [TpController::class, 'store']);
    Route::put('/rombel/{rombel}/tp-urutan', [TpController::class, 'urutan']);
    Route::post('/rombel/{rombel}/tp-salin', [TpController::class, 'salin']);
    Route::put('/tp/{tp}', [TpController::class, 'update']);
    Route::delete('/tp/{tp}', [TpController::class, 'destroy']);

    Route::get('/rombel/{rombel}/nilai', [NilaiController::class, 'index']);
    Route::post('/rombel/{rombel}/nilai/pratinjau', [NilaiController::class, 'pratinjau']);
    Route::put('/rombel/{rombel}/nilai', [NilaiController::class, 'simpan']);
    Route::get('/rombel/{rombel}/rekap', [NilaiController::class, 'rekap']);
    Route::get('/rombel/{rombel}/rekap/ekspor', [NilaiController::class, 'ekspor']);

    Route::get('/rombel/{rombel}/paket', [PaketController::class, 'index']);
    Route::post('/rombel/{rombel}/paket/kirim', [PaketController::class, 'kirim']);
});

// ---------------------------------------------------------------- Wali kelas: verifikasi (hanya kelasnya)
Route::prefix('wali')->middleware('role:WALI_KELAS')->group(function () {
    Route::get('/periode', [PeriodeController::class, 'index']);
    Route::get('/paket', [VerifikasiController::class, 'index']);
    Route::get('/paket/{paket}', [VerifikasiController::class, 'show']);
    Route::post('/paket/{paket}/setujui', [VerifikasiController::class, 'setujui']);
    Route::post('/paket/{paket}/revisi', [VerifikasiController::class, 'revisi'])->name('wali.paket.revisi');
});
