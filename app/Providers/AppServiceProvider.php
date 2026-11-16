<?php

namespace App\Providers;

use App\Models\Semester;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.layouts.admin', function ($view): void {
            $user = auth()->user();

            if ($user) {
                $user->loadMissing([
                    'guru',
                    'siswa.riwayatKelasAktif.kelas',
                ]);
            }

            $semester = Semester::query()
                ->with('tahunAjaran')
                ->where('aktif', true)
                ->orderByDesc('id_semester')
                ->first();

            $role = strtoupper($user?->role ?? '');
            $siswa = $user?->siswa;
            $riwayatKelas = $siswa?->riwayatKelasAktif;
            $namaSemester = match (strtoupper($semester?->nama ?? '')) {
                'GASAL' => 'Ganjil',
                'GENAP' => 'Genap',
                default => $semester?->nama,
            };

            $displayName = match ($role) {
                'ADMIN' => $user?->username,
                'GURU', 'GURU_BK' => $user?->guru?->nama,
                'SISWA' => $siswa?->nama,
                default => null,
            } ?: $user?->username ?: 'Pengguna';

            $roleLabel = match ($role) {
                'ADMIN' => 'Administrator',
                'GURU', 'GURU_BK' => 'Guru Bimbingan dan Konseling',
                'SISWA' => 'Siswa'.($riwayatKelas?->kelas?->nama ? ' · '.$riwayatKelas->kelas->nama : ''),
                default => 'Pengguna',
            };

            $homeRoute = match ($role) {
                'ADMIN' => 'admin.dashboard',
                'GURU', 'GURU_BK' => 'bk.dashboard',
                'SISWA' => 'siswa.dashboard',
                default => 'login',
            };

            $profileRoute = match ($role) {
                'ADMIN' => 'admin.profil',
                'GURU', 'GURU_BK' => 'bk.profil',
                'SISWA' => 'siswa.profil',
                default => 'login',
            };

            $academicYear = collect([$namaSemester, $semester?->tahunAjaran?->nama])
                ->filter()
                ->implode(' · ');

            $view->with('shell', [
                'user' => $user,
                'role' => $role,
                'displayName' => $displayName,
                'roleLabel' => $roleLabel,
                'grade' => $riwayatKelas?->kelas?->tingkat,
                'className' => $riwayatKelas?->kelas?->nama,
                'semester' => $semester,
                'semesterLabel' => $namaSemester,
                'academicYear' => $academicYear !== '' ? $academicYear : 'Semester belum ditetapkan',
                'homeRoute' => $homeRoute,
                'profileRoute' => $profileRoute,
            ]);
        });
    }
}
