<?php

/*
|--------------------------------------------------------------------------
| Route halaman frontend SIAKAD A2 (data dummy)
|--------------------------------------------------------------------------
|
| Dikelompokkan per peran. Nama route dipakai menu sidebar (App\Support\Navigation).
| Saat APP_ENV=local dan FRONTEND_PREVIEW=true, halaman bisa dibuka tanpa login
| dan /preview aktif. Di luar itu semua halaman berada di balik middleware auth.
|
*/

use App\Http\Controllers\Academic\TpController;
use App\Http\Controllers\Admin\KurikulumController;
use App\Http\Controllers\AkunPeranController;
use App\Http\Controllers\BelumTersediaController;
use App\Http\Controllers\BerandaAkademikController;
use App\Http\Controllers\CetakRaporController;
use App\Http\Controllers\DashboardGuruController;
use App\Http\Controllers\DashboardKelasController;
use App\Http\Controllers\DashboardKurikulumController;
use App\Http\Controllers\DashboardOperatorController;
use App\Http\Controllers\DataGuruController;
use App\Http\Controllers\DataKelasController;
use App\Http\Controllers\DataSiswaController;
use App\Http\Controllers\InputNilaiController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\NilaiPerMapelController;
use App\Http\Controllers\PelengkapRaporController;
use App\Http\Controllers\PeriodePenguncianController;
use App\Http\Controllers\RaporSayaController;
use App\Http\Controllers\RekapNilaiController;
use App\Http\Controllers\RelasiOrangTuaController;
use App\Http\Controllers\RiwayatRaporController;
use App\Http\Controllers\RombelController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\VerifikasiNilaiController;
use App\Http\Controllers\WaliKelasController;
use App\Support\Navigation;
use Illuminate\Support\Facades\Route;

$preview = app()->isLocal() && config('app.frontend_preview');

if ($preview) {
    Route::get('/preview', fn () => view('preview.index', [
        'peran' => collect(Navigation::akademik())->map(fn ($menu, $kode) => [
            'label' => Navigation::label($kode),
            'items' => $menu['items'],
        ]),
        'varian' => Navigation::varianPreview(),
    ]))->name('preview');
}

Route::middleware($preview ? [] : ['auth', 'active'])->group(function () {
    // Operator
    Route::prefix('operator')->group(function () {
        Route::get('/dashboard', [DashboardOperatorController::class, 'index'])->name('dashboard-operator.index');
        Route::get('/akun-peran', [AkunPeranController::class, 'index'])->name('akun-peran.index');

        Route::get('/data-siswa', [DataSiswaController::class, 'index'])->name('data-siswa.index');
        Route::get('/data-siswa/format', [DataSiswaController::class, 'format'])->name('data-siswa.format');
        Route::post('/data-siswa/impor', [DataSiswaController::class, 'impor'])->name('data-siswa.impor');
        Route::post('/data-siswa', [DataSiswaController::class, 'store'])->name('data-siswa.store');

        Route::get('/data-guru', [DataGuruController::class, 'index'])->name('data-guru.index');
        Route::get('/data-guru/format', [DataGuruController::class, 'format'])->name('data-guru.format');
        Route::post('/data-guru/impor', [DataGuruController::class, 'impor'])->name('data-guru.impor');
        Route::post('/data-guru', [DataGuruController::class, 'store'])->name('data-guru.store');

        Route::get('/data-kelas', [DataKelasController::class, 'index'])->name('data-kelas.index');
        Route::post('/data-kelas', [DataKelasController::class, 'tambahKelas'])->name('data-kelas.kelas.store');
        Route::post('/data-kelas/{kelas}/siswa', [DataKelasController::class, 'tambahSiswa'])->name('data-kelas.siswa.store');
        Route::post('/data-kelas/pindah', [DataKelasController::class, 'pindah'])->name('data-kelas.pindah');

        Route::get('/relasi-orang-tua', [RelasiOrangTuaController::class, 'index'])->name('relasi-orang-tua.index');
        Route::post('/relasi-orang-tua', [RelasiOrangTuaController::class, 'store'])->name('relasi-orang-tua.store');
        Route::put('/relasi-orang-tua/{ortu}', [RelasiOrangTuaController::class, 'update'])->name('relasi-orang-tua.update');
        Route::delete('/relasi-orang-tua/{ortu}', [RelasiOrangTuaController::class, 'destroy'])->name('relasi-orang-tua.destroy');
    });

    // Admin Kurikulum
    Route::prefix('kurikulum')->group(function () {
        Route::get('/dashboard', [DashboardKurikulumController::class, 'index'])->name('dashboard-kurikulum.index');
        Route::get('/tahun-ajaran', [TahunAjaranController::class, 'index'])->name('tahun-ajaran.index');
        Route::get('/mapel', [MapelController::class, 'index'])->name('mapel.index');
        Route::get('/rombel', [RombelController::class, 'index'])->name('rombel.index');
        Route::get('/rombel/{rombel}', [RombelController::class, 'show'])->name('rombel.show');
        Route::get('/wali-kelas', [WaliKelasController::class, 'index'])->name('wali-kelas.index');
        Route::get('/periode-penguncian', [PeriodePenguncianController::class, 'index'])->name('periode-penguncian.index');
        Route::get('/kktp', [KurikulumController::class, 'kktp'])->name('kktp.index');
        Route::get('/rekap-nilai', [KurikulumController::class, 'rekapNilai'])->name('kurikulum.rekap-nilai.index');
        Route::get('/format-rapor', [KurikulumController::class, 'formatRapor'])->name('format-rapor.index');
        Route::get('/cetak-rapor', [KurikulumController::class, 'cetakRapor'])->name('kurikulum.cetak-rapor.index');
        Route::get('/terbitkan-rapor', [KurikulumController::class, 'terbitkanRapor'])->name('terbitkan-rapor.index');
        Route::get('/arsip-rapor', [KurikulumController::class, 'arsipRapor'])->name('arsip-rapor.index');
    });

    // Guru Mapel
    Route::prefix('guru')->group(function () {
        Route::get('/dashboard', [DashboardGuruController::class, 'index'])->name('dashboard-guru.index');
        Route::get('/tp', [TpController::class, 'index'])->name('tp.index');
        Route::get('/input-nilai', [InputNilaiController::class, 'index'])->name('input-nilai.index');
        Route::get('/rekap-nilai', [RekapNilaiController::class, 'index'])->name('guru.rekap-nilai.index');
        Route::get('/rekap-nilai/ekspor', BelumTersediaController::class)->name('rekap-nilai.export');
        Route::get('/rekap-nilai/{rombel}', BelumTersediaController::class)->name('rekap-nilai.show');
    });

    // Wali Kelas
    Route::prefix('wali-kelas')->group(function () {
        Route::get('/dashboard', [DashboardKelasController::class, 'index'])->name('dashboard-kelas.index');

        Route::get('/verifikasi-nilai', [VerifikasiNilaiController::class, 'index'])->name('verifikasi-nilai.index');
        Route::get('/verifikasi-nilai/{mapel}', [VerifikasiNilaiController::class, 'show'])->name('verifikasi-nilai.show');
        Route::post('/verifikasi-nilai/{mapel}/revisi', [VerifikasiNilaiController::class, 'requestRevision'])->name('verifikasi-nilai.revisi');
        Route::post('/verifikasi-nilai/{mapel}/setujui', [VerifikasiNilaiController::class, 'approve'])->name('verifikasi-nilai.setujui');

        Route::get('/pelengkap-rapor', [PelengkapRaporController::class, 'index'])->name('pelengkap-rapor.index');
        Route::get('/pelengkap-rapor/pratinjau', BelumTersediaController::class)->name('pelengkap-rapor.pratinjau');
        Route::post('/pelengkap-rapor', [PelengkapRaporController::class, 'simpan'])->name('pelengkap-rapor.simpan');
        Route::post('/pelengkap-rapor/{siswa}/catatan', [PelengkapRaporController::class, 'simpanCatatan'])->name('pelengkap-rapor.catatan');

        // TODO: varian wali kelas (desain 4.01–4.03) masih memakai view rekap guru.
        Route::get('/rekap-nilai', [RekapNilaiController::class, 'index'])->name('rekap-nilai.index');

        Route::get('/cetak-rapor', [CetakRaporController::class, 'index'])->name('cetak-rapor.index');
        Route::post('/cetak-rapor/kelas', [CetakRaporController::class, 'unduhKelas'])->name('cetak-rapor.kelas');
        Route::post('/cetak-rapor/siswa/{siswa}', [CetakRaporController::class, 'unduhSiswa'])->name('cetak-rapor.siswa');
        Route::get('/cetak-rapor/riwayat/{riwayat}', [CetakRaporController::class, 'unduhUlang'])->name('cetak-rapor.ulang');
    });

    // Siswa
    Route::prefix('siswa')->group(function () {
        Route::get('/beranda', [BerandaAkademikController::class, 'index'])->name('beranda-akademik.index');
        Route::get('/rapor', [RaporSayaController::class, 'index'])->name('rapor-saya.index');
        Route::get('/nilai', [NilaiPerMapelController::class, 'index'])->name('nilai-per-mapel.index');
        Route::get('/nilai/{id}', [NilaiPerMapelController::class, 'show'])->name('nilai-per-mapel.show');
        Route::get('/riwayat-rapor', [RiwayatRaporController::class, 'index'])->name('riwayat-rapor.index');
    });
});
