<?php

namespace App\Support\Dummy;

/**
 * Data dummy Dashboard Wali Kelas (desain wali-kelas 1.01, dan 5.02 untuk varian "rapor siap").
 * Bentuknya meniru respons API supaya mudah diganti nanti.
 *
 * TODO API: ganti seluruh isi kelas ini dengan data dari database
 * (GET /api/wali/dashboard + /api/wali/paket + /api/wali/pelengkap).
 */
class DashboardWaliKelasDummy
{
    /** @return array<string, mixed> */
    public static function get(bool $raporSiap = false): array
    {
        // TODO API: ganti dengan data dari database (kelas yang diampu wali kelas login).
        $kelas = 'X-B';

        // TODO API: ganti dengan data dari database (mapel + pengampu + progres nilai kelas ini).
        // status: menunggu | disetujui | sedang_diisi | belum_tp | dikembalikan | terkunci
        $mapel = [
            ['id' => 1, 'mapel' => 'Matematika', 'keterangan' => 'Wajib', 'catatan' => null, 'guru' => 'Siti Rahmawati, S.Pd.', 'tp' => 6, 'sts' => 4, 'terisi' => 32, 'total' => 32, 'status' => 'menunggu', 'diperbarui' => '12 Okt'],
            ['id' => 2, 'mapel' => 'Bahasa Indonesia', 'keterangan' => 'Wajib', 'catatan' => null, 'guru' => 'Dra. Endang Lestari', 'tp' => 5, 'sts' => 3, 'terisi' => 32, 'total' => 32, 'status' => 'disetujui', 'diperbarui' => '10 Okt'],
            ['id' => 3, 'mapel' => 'Bahasa Inggris', 'keterangan' => 'Wajib', 'catatan' => null, 'guru' => 'Agus Prasetyo, S.Pd.', 'tp' => 6, 'sts' => 4, 'terisi' => 32, 'total' => 32, 'status' => 'menunggu', 'diperbarui' => '12 Okt'],
            ['id' => 4, 'mapel' => 'Fisika', 'keterangan' => 'Wajib', 'catatan' => null, 'guru' => 'Rudi Hartono, M.Pd.', 'tp' => 5, 'sts' => 3, 'terisi' => 32, 'total' => 32, 'status' => 'disetujui', 'diperbarui' => '9 Okt'],
            ['id' => 5, 'mapel' => 'Biologi', 'keterangan' => 'Wajib', 'catatan' => null, 'guru' => 'Nur Aini, S.Si.', 'tp' => 6, 'sts' => 4, 'terisi' => 27, 'total' => 32, 'status' => 'sedang_diisi', 'diperbarui' => '11 Okt'],
            ['id' => 6, 'mapel' => 'Kimia', 'keterangan' => 'Wajib', 'catatan' => null, 'guru' => 'Bambang Sutrisno, S.Pd.', 'tp' => 5, 'sts' => 3, 'terisi' => 32, 'total' => 32, 'status' => 'dikembalikan', 'diperbarui' => '11 Okt'],
            ['id' => 7, 'mapel' => 'Sejarah', 'keterangan' => 'Wajib', 'catatan' => null, 'guru' => 'Ika Puspitasari, S.Pd.', 'tp' => 4, 'sts' => 2, 'terisi' => 32, 'total' => 32, 'status' => 'disetujui', 'diperbarui' => '8 Okt'],
            ['id' => 8, 'mapel' => 'Pendidikan Pancasila', 'keterangan' => 'Wajib', 'catatan' => null, 'guru' => 'Hadi Santoso, S.Pd.', 'tp' => 4, 'sts' => 2, 'terisi' => 32, 'total' => 32, 'status' => 'menunggu', 'diperbarui' => '11 Okt'],
            ['id' => 9, 'mapel' => 'Informatika', 'keterangan' => 'Wajib', 'catatan' => null, 'guru' => 'Dimas Aditya, S.Kom.', 'tp' => 0, 'sts' => 0, 'terisi' => 0, 'total' => 32, 'status' => 'belum_tp', 'diperbarui' => null],
            ['id' => 10, 'mapel' => 'Pend. Agama Islam dan Budi Pekerti', 'keterangan' => 'Agama · rombel campuran', 'catatan' => 'hanya siswa X-B ditampilkan', 'guru' => 'Ahmad Fauzi, S.Ag.', 'tp' => 5, 'sts' => 3, 'terisi' => 28, 'total' => 28, 'status' => 'disetujui', 'diperbarui' => '7 Okt'],
            ['id' => 11, 'mapel' => 'Bahasa Mandarin', 'keterangan' => 'Bahasa Asing · Mandarin 1', 'catatan' => 'hanya siswa X-B ditampilkan', 'guru' => 'Lin Mei, S.Pd.', 'tp' => 6, 'sts' => 4, 'terisi' => 14, 'total' => 14, 'status' => 'disetujui', 'diperbarui' => '9 Okt'],
            // Halaman 2 (tidak tampak di desain): pola sama, status Disetujui.
            ['id' => 12, 'mapel' => 'Geografi', 'keterangan' => 'Wajib', 'catatan' => null, 'guru' => 'Wahyu Nugroho, S.Pd.', 'tp' => 4, 'sts' => 2, 'terisi' => 32, 'total' => 32, 'status' => 'disetujui', 'diperbarui' => '8 Okt'],
            ['id' => 13, 'mapel' => 'Ekonomi', 'keterangan' => 'Wajib', 'catatan' => null, 'guru' => 'Ratna Dewi, S.E., M.Pd.', 'tp' => 4, 'sts' => 2, 'terisi' => 32, 'total' => 32, 'status' => 'disetujui', 'diperbarui' => '9 Okt'],
            ['id' => 14, 'mapel' => 'PJOK', 'keterangan' => 'Wajib', 'catatan' => null, 'guru' => 'Yusuf Hidayat, S.Pd.', 'tp' => 4, 'sts' => 2, 'terisi' => 32, 'total' => 32, 'status' => 'disetujui', 'diperbarui' => '10 Okt'],
            ['id' => 15, 'mapel' => 'Seni Budaya', 'keterangan' => 'Wajib', 'catatan' => null, 'guru' => 'Maya Kartika, S.Sn.', 'tp' => 4, 'sts' => 2, 'terisi' => 32, 'total' => 32, 'status' => 'disetujui', 'diperbarui' => '9 Okt'],
            ['id' => 16, 'mapel' => 'Bahasa Jawa', 'keterangan' => 'Muatan Lokal', 'catatan' => null, 'guru' => 'Sri Wahyuni, S.Pd.', 'tp' => 4, 'sts' => 2, 'terisi' => 32, 'total' => 32, 'status' => 'disetujui', 'diperbarui' => '10 Okt'],
        ];

        if ($raporSiap) {
            $mapel = array_map(fn (array $m) => [...$m,
                'status' => 'terkunci', 'terisi' => $m['total'], 'tp' => $m['tp'] ?: 4, 'sts' => $m['sts'] ?: 2,
            ], $mapel);
        }

        $jumlah = fn (string ...$status) => count(array_filter($mapel, fn ($m) => in_array($m['status'], $status, true)));
        $menunggu = $jumlah('menunggu');
        $disetujui = $jumlah('disetujui', 'terkunci');
        // Pada desain, mapel "dikirim" = 12/16 dan "disetujui" = 9 (data dummy; TODO API: hitung dari status paket nilai).
        $dikirim = $raporSiap ? count($mapel) : 12;
        $jumlahDisetujui = $raporSiap ? count($mapel) : 9;

        // TODO API: ganti dengan data dari database (anggota kelas + pelengkap rapor).
        $siswa = ['total' => 32, 'laki_laki' => 16, 'perempuan' => 16];
        $catatanWali = $raporSiap ? 32 : 26;
        $ekskul = $raporSiap ? 32 : 30;

        $belumCatatan = $siswa['total'] - $catatanWali;
        $perluTindakan = $raporSiap ? [] : [
            ['jenis' => 'verifikasi', 'id_mapel' => 1, 'judul' => 'Periksa nilai Matematika', 'keterangan' => 'Dikirim 12 Okt oleh Siti Rahmawati', 'aksi' => 'Periksa'],
            ['jenis' => 'verifikasi', 'id_mapel' => 3, 'judul' => 'Periksa nilai Bahasa Inggris', 'keterangan' => 'Dikirim 12 Okt oleh Agus Prasetyo', 'aksi' => 'Periksa'],
            ['jenis' => 'verifikasi', 'id_mapel' => 8, 'judul' => 'Periksa nilai Pendidikan Pancasila', 'keterangan' => 'Dikirim 11 Okt oleh Hadi Santoso', 'aksi' => 'Periksa'],
            ['jenis' => 'tp_kosong', 'id_mapel' => 9, 'judul' => 'Informatika belum memiliki TP', 'keterangan' => 'Guru: Dimas Aditya', 'aksi' => null],
            ['jenis' => 'pelengkap', 'id_mapel' => null, 'judul' => "{$belumCatatan} siswa belum ada catatan wali", 'keterangan' => 'Pelengkap rapor STS', 'aksi' => 'Lengkapi'],
        ];

        return [
            'kelas' => $kelas,
            'tanggal_hari_ini' => 'Senin, 12 Oktober 2026', // TODO API: now()
            'periode' => $raporSiap
                ? ['jenis' => 'STS', 'status' => 'dikunci', 'judul' => "Nilai {$kelas} telah dikunci Kurikulum pada 26 Okt 2026", 'keterangan' => 'Rapor STS siap dicetak.']
                : ['jenis' => 'STS', 'status' => 'dibuka', 'judul' => 'Periode STS dibuka', 'tutup' => 'Jumat, 23 Oktober 2026'],
            'statistik' => [
                'siswa' => $siswa,
                'mapel_dikirim' => $dikirim,
                'mapel_total' => count($mapel),
                'menunggu_verifikasi' => $menunggu,
                'catatan_wali_terisi' => $catatanWali,
            ],
            // state: selesai | aktif | berjalan | menunggu
            'langkah' => $raporSiap ? [
                ['judul' => 'Nilai dikirim guru', 'keterangan' => count($mapel).'/'.count($mapel).' mapel', 'state' => 'selesai'],
                ['judul' => 'Diverifikasi wali kelas', 'keterangan' => "{$disetujui} disetujui", 'state' => 'selesai'],
                ['judul' => 'Pelengkap rapor', 'keterangan' => "{$catatanWali}/{$siswa['total']} siswa", 'state' => 'selesai'],
                ['judul' => 'Dikunci Kurikulum', 'keterangan' => '26 Okt 2026', 'state' => 'selesai'],
                ['judul' => 'Rapor siap dicetak', 'keterangan' => 'Siap dicetak', 'state' => 'selesai'],
            ] : [
                ['judul' => 'Nilai dikirim guru', 'keterangan' => "{$dikirim}/".count($mapel).' mapel', 'state' => 'selesai'],
                ['judul' => 'Diverifikasi wali kelas', 'keterangan' => "{$jumlahDisetujui} disetujui", 'state' => 'aktif'],
                ['judul' => 'Pelengkap rapor', 'keterangan' => "{$catatanWali}/{$siswa['total']} siswa", 'state' => 'berjalan'],
                ['judul' => 'Dikunci Kurikulum', 'keterangan' => 'Menunggu', 'state' => 'menunggu'],
                ['judul' => 'Rapor siap dicetak', 'keterangan' => 'Belum tersedia', 'state' => 'menunggu'],
            ],
            'mapel' => $mapel,
            'perlu_tindakan' => $perluTindakan,
            'pelengkap' => [
                'catatan_wali' => ['terisi' => $catatanWali, 'total' => $siswa['total']],
                'kehadiran_tersedia' => true, // dari Presensi A1
                'ekskul' => ['terisi' => $ekskul, 'total' => $siswa['total']], // dari Kesiswaan
            ],
            'status_rapor' => $raporSiap
                ? ['bisa_cetak' => true, 'judul' => 'Rapor siap dicetak', 'keterangan' => 'Nilai dikunci Kurikulum pada 26 Okt 2026.']
                : ['bisa_cetak' => false, 'judul' => 'Rapor belum dapat dicetak', 'keterangan' => 'Menunggu seluruh mapel disetujui dan dikunci oleh Kurikulum.'],
        ];
    }
}
