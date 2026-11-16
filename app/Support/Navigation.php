<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Menu sidebar dan data header per peran, satu sumber untuk seluruh halaman frontend.
 *
 * Peran ditentukan dari segmen pertama URL (/operator, /kurikulum, /guru, /wali-kelas, /siswa).
 * Ikon terbatas pada ikon yang tersedia di komponen sidebar; ikon desain yang belum ada
 * (kunci, buku, target, megafon, arsip, dll.) dipetakan ke ikon terdekat.
 *
 * TODO API: GET /api/auth/me (nama, peran aktif, kelas wali) dan semester aktif.
 */
class Navigation
{
    public const PERAN = ['operator', 'kurikulum', 'guru', 'wali-kelas', 'siswa'];

    public static function peran(?Request $request = null): ?string
    {
        $segmen = ($request ?? request())->segment(1);

        return in_array($segmen, self::PERAN, true) ? $segmen : null;
    }

    /**
     * Item A2 Akademik per peran: label, ikon, nama route, pola route aktif.
     *
     * @return array<string, array{judul: ?string, items: list<array<string, string>>}>
     */
    public static function akademik(): array
    {
        return [
            'operator' => [
                'judul' => 'Operator',
                'items' => [
                    self::item('Dashboard', 'grid', 'dashboard-operator.index'),
                    self::item('Akun & Peran', 'shield', 'akun-peran.index'),
                    self::item('Data Siswa', 'file', 'data-siswa.index'),
                    self::item('Data Guru', 'file', 'data-guru.index'),
                    self::item('Data Kelas', 'home', 'data-kelas.index'),
                    self::item('Relasi Orang Tua-Anak', 'check', 'relasi-orang-tua.index'),
                ],
            ],
            'kurikulum' => [
                'judul' => 'Kurikulum',
                'items' => [
                    self::item('Dashboard', 'grid', 'dashboard-kurikulum.index'),
                    self::item('Tahun Ajaran', 'calendar', 'tahun-ajaran.index'),
                    self::item('Mata Pelajaran', 'file', 'mapel.index'),
                    self::item('Rombel & Pengampu', 'home', 'rombel.index'),
                    self::item('Wali Kelas', 'check', 'wali-kelas.index'),
                    self::item('Periode & Penguncian', 'shield', 'periode-penguncian.index'),
                    self::item('KKTP & Deskripsi', 'pencil', 'kktp.index'),
                    self::item('Rekap Nilai (DKN)', 'table', 'kurikulum.rekap-nilai.index'),
                    self::item('Format Rapor', 'file', 'format-rapor.index'),
                    self::item('Cetak Rapor', 'printer', 'kurikulum.cetak-rapor.index'),
                    self::item('Terbitkan Rapor', 'scan', 'terbitkan-rapor.index'),
                    self::item('Arsip Rapor', 'history', 'arsip-rapor.index'),
                ],
            ],
            'guru' => [
                'judul' => 'Guru',
                'items' => [
                    self::item('Dashboard', 'grid', 'dashboard-guru.index'),
                    self::item('Kelola TP', 'check', 'tp.index'),
                    self::item('Input Nilai', 'pencil', 'input-nilai.index'),
                    self::item('Rekap Nilai (DKN)', 'table', 'guru.rekap-nilai.index'),
                ],
            ],
            'wali-kelas' => [
                'judul' => 'Wali Kelas',
                'items' => [
                    self::item('Dashboard Kelas', 'grid', 'dashboard-kelas.index'),
                    self::item('Verifikasi Nilai', 'check', 'verifikasi-nilai.index'),
                    self::item('Pelengkap Rapor', 'pencil', 'pelengkap-rapor.index'),
                    self::item('Rekap Nilai (DKN)', 'table', 'rekap-nilai.index'),
                    self::item('Cetak Rapor', 'printer', 'cetak-rapor.index'),
                ],
            ],
            'siswa' => [
                'judul' => null,
                'items' => [
                    self::item('Beranda Akademik', 'home', 'beranda-akademik.index'),
                    self::item('Rapor Saya', 'file', 'rapor-saya.index'),
                    self::item('Nilai per Mapel', 'table', 'nilai-per-mapel.index'),
                    self::item('Riwayat Rapor', 'history', 'riwayat-rapor.index'),
                ],
            ],
        ];
    }

    /**
     * Menu lengkap (A1–A4) dalam format prop `menu` komponen sidebar.
     * Item A2 yang halamannya belum dibuat (route belum terdaftar) disembunyikan.
     */
    public static function menu(string $peran): array
    {
        $a2 = self::akademik()[$peran];
        $tersedia = array_values(array_filter($a2['items'], fn ($item) => Route::has($item['route'])));

        return [
            ['code' => 'A1', 'label' => 'Presensi', 'icon' => 'calendar', 'sub_title' => 'Presensi', 'items' => self::presensi($peran)],
            ['code' => 'A2', 'label' => 'Akademik', 'icon' => 'file', 'sub_title' => $a2['judul'], 'items' => $tersedia],
            ['code' => 'A3', 'label' => 'BK', 'icon' => 'shield', 'href' => '#'],
            ['code' => 'A4', 'label' => 'Portal Orang Tua', 'icon' => 'home', 'href' => '#'],
        ];
    }

    /** Data header per peran (nama sesuai desain). */
    public static function topbar(string $peran): array
    {
        return [
            'operator' => ['name' => 'Andi Pratama', 'role' => 'Operator'],
            'kurikulum' => ['name' => 'Dra. Wahyuni, M.Pd.', 'role' => 'Admin Kurikulum', 'initials' => 'WH'],
            'guru' => ['name' => 'Siti Rahmawati, S.Pd.', 'role' => 'Guru · Matematika'],
            'wali-kelas' => ['name' => 'Siti Rahmawati, S.Pd.', 'role' => 'Wali Kelas · X-B'],
            'siswa' => ['name' => 'Citra Lestari', 'role' => 'Siswa · X-B'],
        ][$peran];
    }

    /**
     * Variasi state/modal per halaman untuk /preview: route => [label => parameter query].
     * Parameter '_route' dipakai untuk halaman turunan (mis. detail).
     */
    public static function varianPreview(): array
    {
        return [
            'dashboard-guru.index' => ['05 · Kosong' => ['state' => 'kosong']],
            'tahun-ajaran.index' => ['4.02 · Modal aktifkan semester' => ['modal' => 'aktifkan']],
            'mapel.index' => ['4.04 · Modal tambah mapel' => ['modal' => 'mapel']],
            'rombel.index' => [
                '4.06 · Dropdown pilih guru' => ['pilih-guru' => 'jerman-1'],
                '4.07 · Modal buat rombel' => ['modal' => 'buat-rombel'],
                '4.08 · Detail rombel' => ['_route' => 'rombel.show', 'rombel' => 'mandarin-1'],
                '4.09 · Modal tambah peserta' => ['_route' => 'rombel.show', 'rombel' => 'mandarin-1', 'modal' => 'tambah-peserta'],
            ],
            'wali-kelas.index' => ['4.10 · 2 perubahan belum disimpan' => ['state' => 'diubah']],
        ];
    }

    public static function label(string $peran): string
    {
        return [
            'operator' => 'Operator',
            'kurikulum' => 'Admin Kurikulum',
            'guru' => 'Guru Mapel',
            'wali-kelas' => 'Wali Kelas',
            'siswa' => 'Siswa',
        ][$peran];
    }

    /** Menu A1 (milik kelompok A1), disamakan dengan pemetaan di komponen sidebar. */
    private static function presensi(string $peran): array
    {
        $riwayat = ['label' => 'Riwayat Presensi', 'icon' => 'history', 'route' => 'presensi.riwayat'];
        $edit = ['label' => 'Edit Presensi', 'icon' => 'pencil', 'route' => 'presensi.edit'];
        $scan = ['label' => 'Scan Presensi', 'icon' => 'scan', 'route' => 'presensi.scan'];

        return match ($peran) {
            'siswa' => [
                ['label' => 'Presensi QR Code', 'icon' => 'qr', 'route' => 'presensi.qr-code'],
                ['label' => 'Presensi Manual', 'icon' => 'map-pin', 'route' => 'presensi.manual'],
                $riwayat,
            ],
            'operator', 'kurikulum' => [$riwayat, $edit, $scan],
            default => [$riwayat, $edit],
        };
    }

    private static function item(string $label, string $icon, string $route): array
    {
        $pola = str_ends_with($route, '.index')
            ? substr($route, 0, -strlen('.index')).'.*'
            : $route;

        return ['label' => $label, 'icon' => $icon, 'route' => $route, 'pattern' => $pola];
    }
}
