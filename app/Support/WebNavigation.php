<?php

namespace App\Support;

class WebNavigation
{
    public static function dashboard(): string
    {
        return array_key_first(self::links()) ?? '/ganti-sandi';
    }

    public static function links(): array
    {
        return match (AcademicAccess::peranAktif()) {
            'GURU' => ['/guru/dashboard' => 'Dashboard Guru', '/guru/tp' => 'Kelola TP', '/guru/input-nilai' => 'Input Nilai'],
            'WALI_KELAS' => ['/wali-kelas/dashboard' => 'Dashboard Kelas', '/wali-kelas/verifikasi-nilai' => 'Verifikasi Nilai', '/wali-kelas/rekap-nilai' => 'Rekap Nilai', '/wali-kelas/cetak-rapor' => 'Cetak dan Arsip Rapor'],
            'KURIKULUM' => ['/kurikulum/dashboard' => 'Dashboard Kurikulum', '/kurikulum/data-akademik' => 'Data Akademik', '/kurikulum/periode-penguncian' => 'Periode dan Penguncian', '/kurikulum/rekap-nilai' => 'Rekap Nilai', '/kurikulum/cetak-rapor' => 'Cetak dan Terbitkan Rapor'],
            'ADMIN' => ['/operator/dashboard' => 'Dashboard Operator', '/operator/data-master' => 'Data Master'],
            'SUPERADMIN' => ['/superadmin/dashboard' => 'Dashboard Super Admin'],
            'KESISWAAN' => ['/kesiswaan/dashboard' => 'Dashboard Kesiswaan', '/kesiswaan/ekskul' => 'Predikat Ekstrakurikuler'],
            'BUDAYA_POSITIF' => ['/bk/dashboard' => 'Dashboard Guru BK'],
            'SISWA' => ['/siswa/beranda' => 'Beranda', '/siswa/nilai' => 'Nilai per Mapel', '/siswa/rapor' => 'Rapor Saya'],
            'ORANG_TUA' => ['/orang-tua/beranda' => 'Beranda', '/orang-tua/nilai' => 'Nilai Anak', '/orang-tua/rapor' => 'Rapor Anak'],
            default => [],
        };
    }
}
