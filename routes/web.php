<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\PaketNilaiController;
use App\Http\Controllers\PeriodeNilaiController;
use App\Http\Controllers\RombelController;
use App\Http\Controllers\SekolahController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\SiswaKelasController;
use App\Http\Controllers\TahunAjaranController;
use App\Http\Controllers\TpController;
use App\Http\Controllers\UserAccessController;
use App\Http\Controllers\WaliKelasController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['application' => 'SIAKAD A2', 'status' => 'ok']));
// Sengaja di web.php: semua endpoint memakai cookie session dan proteksi CSRF.
Route::prefix('api')->group(function () {
    Route::get('csrf-token', [AuthController::class, 'csrf']);
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:register');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::middleware(['auth', 'active'])->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::prefix('admin')->middleware('role:admin')->group(function () {
            Route::get('roles', [UserAccessController::class, 'roles']);
            Route::get('users', [UserAccessController::class, 'index']);
            Route::patch('users/{user}/access', [UserAccessController::class, 'update']);
            Route::put('users/{user}/profile', [UserAccessController::class, 'linkProfile']);
            Route::get('logs', [UserAccessController::class, 'logs']);
        });
        Route::middleware('role:admin,kurikulum')->group(function () {
            Route::get('sekolah', [SekolahController::class, 'show']);
            Route::put('sekolah', [SekolahController::class, 'update']);
            Route::get('siswa-kelas', [SiswaKelasController::class, 'index']);
            Route::post('siswa-kelas', [SiswaKelasController::class, 'store']);
            Route::get('tahun-ajaran', [TahunAjaranController::class, 'index']);
            Route::post('tahun-ajaran', [TahunAjaranController::class, 'store']);
            Route::get('tahun-ajaran/{record}', [TahunAjaranController::class, 'show']);
            Route::put('tahun-ajaran/{record}', [TahunAjaranController::class, 'update']);
            Route::get('semester', [SemesterController::class, 'index']);
            Route::post('semester', [SemesterController::class, 'store']);
            Route::get('semester/{record}', [SemesterController::class, 'show']);
            Route::put('semester/{record}', [SemesterController::class, 'update']);
            Route::get('kelas', [KelasController::class, 'index']);
            Route::post('kelas', [KelasController::class, 'store']);
            Route::get('kelas/{record}', [KelasController::class, 'show']);
            Route::put('kelas/{record}', [KelasController::class, 'update']);
            Route::get('mapel', [MapelController::class, 'index']);
            Route::post('mapel', [MapelController::class, 'store']);
            Route::get('mapel/{record}', [MapelController::class, 'show']);
            Route::put('mapel/{record}', [MapelController::class, 'update']);
            Route::get('siswa', [SiswaController::class, 'index']);
            Route::post('siswa', [SiswaController::class, 'store']);
            Route::get('siswa/{record}', [SiswaController::class, 'show']);
            Route::put('siswa/{record}', [SiswaController::class, 'update']);
            Route::get('guru', [GuruController::class, 'index']);
            Route::post('guru', [GuruController::class, 'store']);
            Route::get('guru/{record}', [GuruController::class, 'show']);
            Route::put('guru/{record}', [GuruController::class, 'update']);
        });

        // ===== Tahap A–D (v2): rombel, wali kelas, TP, nilai, periode, paket =====

        // Pembacaan periode & paket (wali melihat kelasnya saja, dibatasi di controller).
        Route::middleware('role:admin,kurikulum,guru,wali_kelas')->group(function () {
            Route::get('periode-nilai', [PeriodeNilaiController::class, 'index']);
            Route::get('paket-nilai', [PaketNilaiController::class, 'index']);
        });

        // Guru pengampu / kurikulum: rombel, TP, nilai (pengecekan rombel di controller).
        Route::middleware('role:admin,kurikulum,guru')->group(function () {
            Route::get('rombel', [RombelController::class, 'index']);
            Route::get('rombel/{record}', [RombelController::class, 'show']);
            Route::get('rombel/{record}/tp', [TpController::class, 'index']);
            Route::post('rombel/{record}/tp', [TpController::class, 'store']);
            Route::put('tp/{tp}', [TpController::class, 'update']);
            Route::delete('tp/{tp}', [TpController::class, 'destroy']);
            Route::post('tp/salin', [TpController::class, 'salin']);
            Route::get('rombel/{record}/nilai', [NilaiController::class, 'index']);
            Route::put('rombel/{record}/nilai', [NilaiController::class, 'simpan']);
            Route::post('rombel/{record}/nilai/pratinjau-tempel', [NilaiController::class, 'pratinjauTempel']);
        });

        // Guru mengirim nilai paket kelasnya.
        Route::middleware('role:guru')->post('paket-nilai/kirim', [PaketNilaiController::class, 'kirim']);

        // Wali kelas memeriksa paket kelasnya.
        Route::middleware('role:wali_kelas')->group(function () {
            Route::post('paket-nilai/{record}/setujui', [PaketNilaiController::class, 'setujui']);
            Route::post('paket-nilai/{record}/revisi', [PaketNilaiController::class, 'revisi']);
        });

        // Kurikulum: penugasan, periode, kunci, koreksi.
        Route::middleware('role:admin,kurikulum')->group(function () {
            Route::post('rombel', [RombelController::class, 'store']);
            Route::put('rombel/{record}', [RombelController::class, 'update']);
            Route::put('rombel/{record}/guru', [RombelController::class, 'setGuru']);
            Route::post('rombel/{record}/peserta', [RombelController::class, 'tambahPeserta']);
            Route::put('kelas/{record}/wali-kelas', [WaliKelasController::class, 'set']);
            Route::post('periode-nilai', [PeriodeNilaiController::class, 'store']);
            Route::put('periode-nilai/{record}', [PeriodeNilaiController::class, 'update']);
            Route::post('periode-nilai/{record}/kunci', [PeriodeNilaiController::class, 'kunci']);
            Route::post('paket-nilai/{record}/kunci', [PaketNilaiController::class, 'kunci']);
            Route::post('paket-nilai/{record}/buka-koreksi', [PaketNilaiController::class, 'bukaKoreksi']);
        });
    });
});
