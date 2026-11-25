<?php

use App\Http\Controllers\Portal\PortalController;
use App\Http\Controllers\Rapor\DknController;
use App\Http\Controllers\Rapor\RaporController;
use App\Http\Controllers\Rapor\TemplateRaporController;
use App\Http\Controllers\Wali\KelasWaliController;
use App\Http\Controllers\Wali\PelengkapController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------- Kurikulum: DKN, template, cetak, terbit
Route::prefix('kurikulum')->middleware('role:KURIKULUM')->group(function () {
    Route::get('/dkn', [DknController::class, 'show']);
    Route::get('/dkn/ekspor', [DknController::class, 'ekspor']);

    Route::get('/template-rapor', [TemplateRaporController::class, 'index']);
    Route::post('/template-rapor', [TemplateRaporController::class, 'store']);
    Route::put('/template-rapor/{template}', [TemplateRaporController::class, 'update']);

    Route::get('/rapor', [RaporController::class, 'index']);
    Route::post('/rapor/cetak', [RaporController::class, 'cetakSiswa']);
    Route::post('/rapor/cetak-kelas', [RaporController::class, 'cetakKelas']);
    Route::get('/rapor/riwayat-cetak', [RaporController::class, 'riwayatCetak']);
    Route::get('/rapor/{rapor}/unduh', [RaporController::class, 'unduh']);
    Route::post('/rapor/terbitkan', [RaporController::class, 'terbitkan']);
    Route::post('/rapor/{rapor}/cabut', [RaporController::class, 'cabut']);
});

// ---------------------------------------------------------------- Wali kelas: dashboard, pelengkap, DKN, cetak
Route::prefix('wali')->middleware('role:WALI_KELAS')->group(function () {
    Route::get('/dashboard', [KelasWaliController::class, 'dashboard']);

    Route::get('/pelengkap', [PelengkapController::class, 'index']);
    Route::put('/pelengkap', [PelengkapController::class, 'simpan']);
    Route::post('/pelengkap/tarik-presensi', [PelengkapController::class, 'tarikPresensi']);

    Route::get('/dkn', [DknController::class, 'show']);
    Route::get('/dkn/ekspor', [DknController::class, 'ekspor']);

    Route::get('/rapor', [RaporController::class, 'index']);
    Route::post('/rapor/cetak', [RaporController::class, 'cetakSiswa']);
    Route::post('/rapor/cetak-kelas', [RaporController::class, 'cetakKelas']);
    Route::get('/rapor/riwayat-cetak', [RaporController::class, 'riwayatCetak']);
    Route::get('/rapor/{rapor}/unduh', [RaporController::class, 'unduh']);
});

// ---------------------------------------------------------------- Siswa & orang tua (data dirinya / anaknya saja)
Route::prefix('portal')->middleware('role:SISWA,ORANG_TUA')->group(function () {
    Route::get('/anak', [PortalController::class, 'anak']);
    Route::get('/beranda', [PortalController::class, 'beranda']);
    Route::get('/nilai', [PortalController::class, 'nilai']);
    Route::get('/nilai/{rombel}', [PortalController::class, 'nilaiDetail']);
    Route::get('/rapor', [PortalController::class, 'rapor']);
    Route::get('/rapor/{rapor}/unduh', [PortalController::class, 'unduh']);
});
