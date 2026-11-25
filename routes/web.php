
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InputNilaiController;
use App\Http\Controllers\VerifikasiNilaiController;
use App\Http\Controllers\RekapNilaiController;
use App\Http\Controllers\PeriodePenguncianController;
use App\Http\Controllers\PelengkapRaporController;
use App\Http\Controllers\CetakRaporController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QrSiswaController;

// ==========================================
// PEMBAGIAN MODUL PROYEK NAVA
// ==========================================
// A1 - PRESENSI
// A2 - AKADEMIK
// A3 - BK (BIMBINGAN KONSELING)
// A4 - PORTAL ORANG TUA
// ==========================================

Route::get('/', function () {
    return redirect()->route('login');
});

Route::view('/test-sidebar', 'test-sidebar');

// ==========================================
// A1 - PRESENSI
// ==========================================

// FE - Halaman Login & Register
Route::view('/login', 'auth.login')->name('login');

Route::post('/login', [AuthController::class, 'loginWeb'])
    ->name('login.submit');

Route::view('/lupa-password', 'auth.lupa-password')
    ->name('password.request');

Route::view('/reset-password/{token}', 'auth.reset-password')
    ->name('password.reset');

Route::post('/logout', [AuthController::class, 'logoutWeb'])
    ->middleware('auth')
    ->name('logout');

Route::view('/register', 'auth.register')->name('register');

Route::view('/ganti-password', 'auth.ganti-password')
    ->middleware('auth')
    ->name('password.change');

Route::prefix('presensi')
    ->name('presensi.')
    ->middleware('auth')
    ->group(function () {

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
    
    Route::get('/siswa/qr', [QrSiswaController::class, 'showWeb'])
        ->middleware('auth')
        ->name('siswa.qr');

        Route::get('/siswa/riwayat', function (\Illuminate\Http\Request $request) {
        $user = $request->user();

        if (!$user || $user->role !== 'SISWA' || !$user->siswa) {
            abort(403);
        }

        return app(PresensiController::class)->riwayatSiswa(
            $request,
            (int) $user->siswa->id_siswa
        );
    })->middleware('auth')->name('siswa.riwayat');

    // ROLE SISWA
    Route::get('/siswa', function (\Illuminate\Http\Request $request) {
        $user = $request->user();

        if (!$user || $user->role !== 'SISWA' || !$user->siswa) {
            abort(403);
        }

        $semesterAktif = \App\Models\Semester::where('aktif', true)
            ->whereHas('tahunAjaran', function ($query) {
                $query->where('aktif', true);
            })
            ->first();

        $riwayatKelas = $semesterAktif
            ? $user->siswa->riwayatKelas()
                ->with('kelas')
                ->where('id_semester', $semesterAktif->id_semester)
                ->first()
            : null;

        return view('presensi.siswa.index', [
            'dataSiswa' => [
                'nama' => $user->siswa->nama,
                'kelas' => $riwayatKelas?->kelas?->nama ?? '',
            ],
        ]);
    })->name('siswa');

    // ROLE GURU / WALI KELAS
    Route::view('/guru', 'presensi.guru.index', [
        'pageTitle' => 'Kelola Presensi',
        'rolePresensi' => 'guru',
        'namaProfil' => 'Nama Guru',
        'labelProfil' => 'Guru Wali Kelas',
        'detailProfil' => 'XI MIPA 2',
    ])->name('guru');

    // ROLE KURIKULUM
    Route::view('/kurikulum', 'presensi.guru.index', [
        'pageTitle' => 'Dashboard',
        'rolePresensi' => 'kurikulum',
        'namaProfil' => 'Kurikulum',
        'labelProfil' => 'Kurikulum',
        'detailProfil' => 'SMAN 1 Malang',
    ])->name('kurikulum');

    // ROLE ADMIN
    Route::view('/admin/riwayat', 'presensi.guru.index', [
        'pageTitle' => 'Riwayat Presensi',
        'rolePresensi' => 'admin',
        'namaProfil' => 'Admin',
        'labelProfil' => 'Administrator',
        'detailProfil' => 'SMAN 1 Malang',
    ])->middleware('role:ADMIN')->name('admin.riwayat');
    
    Route::view('/admin', 'presensi.admin.index')
        ->name('admin');


    // ROLE BUDAYA POSITIF
    Route::view('/budaya-positif', 'presensi.guru.index', [
        'pageTitle' => 'Dashboard',
        'rolePresensi' => 'budaya-positif',
        'namaProfil' => 'Budaya Positif',
        'labelProfil' => 'Budaya Positif',
        'detailProfil' => 'SMAN 1 Malang',
    ])->middleware('role:BUDAYA_POSITIF')->name('budaya-positif');

    Route::view('/budaya-positif/admin-presensi', 'presensi.admin.index')
        ->middleware('role:BUDAYA_POSITIF,ADMIN')
        ->name('budaya-positif.admin-presensi');

});

// ==========================================
// A2 - AKADEMIK
// ==========================================

// Input Nilai
Route::get('/input-nilai', [InputNilaiController::class, 'index'])
    ->name('input-nilai.index');

// Verifikasi Nilai
Route::prefix('verifikasi-nilai')
    ->name('verifikasi-nilai.')
    ->group(function () {
        Route::get('/', [VerifikasiNilaiController::class, 'index'])->name('index');
        Route::get('/{mapel}', [VerifikasiNilaiController::class, 'show'])->name('show');
        Route::post('/{mapel}/revisi', [VerifikasiNilaiController::class, 'requestRevision'])->name('revisi');
        Route::post('/{mapel}/setujui', [VerifikasiNilaiController::class, 'approve'])->name('setujui');
    });

// Rekap Nilai
Route::get('/rekap-nilai', [RekapNilaiController::class, 'index'])
    ->name('rekap-nilai.index');

// Periode dan Penguncian
Route::get('/periode-penguncian', [PeriodePenguncianController::class, 'index'])
    ->name('periode-penguncian.index');

// Pelengkap Rapor
Route::prefix('pelengkap-rapor')
    ->name('pelengkap-rapor.')
    ->group(function () {
        Route::get('/', [PelengkapRaporController::class, 'index'])->name('index');
        Route::post('/simpan', [PelengkapRaporController::class, 'simpan'])->name('simpan');
        Route::post('/siswa/{siswa}/catatan', [PelengkapRaporController::class, 'simpanCatatan'])->name('catatan');
    });

// Cetak dan Ekspor Rapor
Route::prefix('cetak-rapor')
    ->name('cetak-rapor.')
    ->group(function () {
        Route::get('/', [CetakRaporController::class, 'index'])->name('index');
        Route::post('/kelas/unduh', [CetakRaporController::class, 'unduhKelas'])->name('kelas');
        Route::post('/siswa/{siswa}/unduh', [CetakRaporController::class, 'unduhSiswa'])->name('siswa');
        Route::get('/riwayat/{riwayat}/unduh', [CetakRaporController::class, 'unduhUlang'])->name('ulang');
    });

// Placeholder Akademik
Route::get('/rekap-nilai/export/{scope}', function ($scope) {
    return 'Export ' . $scope . ' belum dibuat';
})->name('rekap-nilai.export');

Route::get('/rekap-nilai/rombel/{rombel}', function ($rombel) {
    return 'Detail rombel ' . $rombel . ' belum dibuat';
})->name('rekap-nilai.show');

Route::get('/kelola-tp', function () {
    return 'Halaman Kelola TP belum dibuat';
})->name('tp.index');

// ==========================================
// A3 - BK (BIMBINGAN DAN KONSELING)
// Route BK dipertahankan sesuai implementasi kelompok A3.
// ==========================================

// ==========================================
// A4 - PORTAL ORANG TUA
// Route Portal Orang Tua dipertahankan sesuai implementasi kelompok A4.
// ==========================================
