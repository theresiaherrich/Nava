<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Academic\TpController;
use App\Http\Controllers\Admin\KurikulumController;
use App\Http\Controllers\DashboardKelasController;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')->name('login.attempt');
    Route::get('/forgot-password', [PasswordController::class, 'forgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordController::class, 'sendResetLink'])
        ->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordController::class, 'resetPassword'])->name('password.update');
});

// --- PINDAHKAN KE SINI SEMENTARA UNTUK BYPASS LOGIN ---
Route::get('/academic/tp', [TpController::class, 'index'])->name('tp.index');
// Dashboard Wali Kelas (data dummy). TODO (backend): pindahkan ke grup middleware auth.
Route::get('/dashboard-kelas', [DashboardKelasController::class, 'index'])->name('dashboard-kelas.index');
// ------------------------------------------------------

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::put('/password', [PasswordController::class, 'changePassword'])->name('password.change');
    Route::post('/admin/users', [UserController::class, 'store'])
        ->middleware('can:users.create')->name('admin.users.store');
    
    // Baris /academic/tp sebelumnya ada di sini, sekarang dipindah ke atas.
});

Route::prefix('admin/kurikulum')->group(function () {
    Route::get('/kktp', [KurikulumController::class, 'kktp'])->name('admin.kktp');
    Route::get('/rekap-nilai', [KurikulumController::class, 'rekapNilai'])->name('admin.rekap-nilai');
    Route::get('/format-rapor', [KurikulumController::class, 'formatRapor'])->name('admin.format-rapor');
    Route::get('/cetak-rapor', [KurikulumController::class, 'cetakRapor'])->name('admin.cetak-rapor');
    Route::get('/terbitkan-rapor', [KurikulumController::class, 'terbitkanRapor'])->name('admin.terbitkan-rapor');
    Route::get('/arsip-rapor', [KurikulumController::class, 'arsipRapor'])->name('admin.arsip-rapor');
});