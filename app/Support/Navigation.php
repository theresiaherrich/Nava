<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Menu sidebar dan data header per peran, satu sumber untuk seluruh halaman frontend.
 *
 * Peran ditentukan dari segmen pertama URL (/operator, /kurikulum, /guru, /wali-kelas, /siswa).
 * Nama ikon merujuk ke daftar ikon di komponen sidebar (resources/views/components/sidebar.blade.php).
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
                    self::item('Akun & Peran', 'key', 'akun-peran.index'),
                    self::item('Data Siswa', 'user', 'data-siswa.index'),
                    self::item('Data Guru', 'users', 'data-guru.index'),
                    self::item('Data Kelas', 'building', 'data-kelas.index'),
                    self::item('Relasi Orang Tua-Anak', 'link', 'relasi-orang-tua.index'),
                ],
            ],
            'kurikulum' => [
                'judul' => 'Kurikulum',
                'items' => [
                    self::item('Dashboard', 'grid', 'dashboard-kurikulum.index'),
                    self::item('Tahun Ajaran', 'calendar', 'tahun-ajaran.index'),
                    self::item('Mata Pelajaran', 'book', 'mapel.index'),
                    self::item('Rombel & Pengampu', 'layers', 'rombel.index'),
                    self::item('Wali Kelas', 'user-star', 'wali-kelas.index'),
                    self::item('Periode & Penguncian', 'lock', 'periode-penguncian.index'),
                    self::item('KKTP & Deskripsi', 'sliders', 'kktp.index'),
                    self::item('Rekap Nilai (DKN)', 'table', 'kurikulum.rekap-nilai.index'),
                    self::item('Format Rapor', 'file-text', 'format-rapor.index'),
                    self::item('Cetak Rapor', 'printer', 'kurikulum.cetak-rapor.index'),
                    self::item('Terbitkan Rapor', 'megaphone', 'terbitkan-rapor.index'),
                    self::item('Arsip Rapor', 'archive', 'arsip-rapor.index'),
                ],
            ],
            'guru' => [
                'judul' => 'Guru',
                'items' => [
                    self::item('Dashboard', 'grid', 'dashboard-guru.index'),
                    self::item('Kelola TP', 'target', 'tp.index'),
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
                    self::item('Rapor Saya', 'file-text', 'rapor-saya.index'),
                    self::item('Nilai per Mapel', 'table', 'nilai-per-mapel.index'),
                    self::item('Riwayat Rapor', 'clock', 'riwayat-rapor.index'),
                ],
            ],
        ];
    }

    /**
     * Menu sidebar dalam format prop `menu` komponen sidebar.
     * - Item A2 yang halamannya belum dibuat (route belum terdaftar) disembunyikan.
     * - Portal Orang Tua (A4) tidak masuk sidebar: orang tua memakai web tersendiri.
     * - Kode modul (A1, A2, …) tidak ditampilkan komponen sidebar (keputusan tim);
     *   tetap dikirim sebagai metadata.
     */
    public static function menu(string $peran): array
    {
        $a2 = self::akademik()[$peran];
        $tersedia = array_values(array_filter($a2['items'], fn ($item) => Route::has($item['route'])));

        return [
            ['code' => 'A1', 'label' => 'Presensi', 'icon' => 'calendar', 'sub_title' => 'Presensi', 'items' => self::presensi($peran)],
            ['code' => 'A2', 'label' => 'Akademik', 'icon' => 'file', 'sub_title' => $a2['judul'], 'items' => $tersedia],
            ['code' => 'A3', 'label' => 'BK', 'icon' => 'shield', 'href' => '#'],
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
            // Operator
            'akun-peran.index' => ['2.03 · Modal tambah akun (username bentrok)' => ['modal' => 'akun']],
            'data-siswa.index' => ['2.05 · Modal impor data siswa' => ['modal' => 'impor']],
            'data-guru.index' => ['Modal impor data guru' => ['modal' => 'impor']],
            'data-kelas.index' => ['2.08 · Modal pindahkan siswa' => ['modal' => 'pindah']],
            'relasi-orang-tua.index' => ['2.10 · Modal hubungkan orang tua' => ['modal' => 'hubungkan']],
            // Guru & Kurikulum
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
