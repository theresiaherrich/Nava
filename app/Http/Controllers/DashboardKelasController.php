<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class DashboardKelasController extends Controller
{
    public function index(Request $request)
    {
        // TODO (backend): ambil dari user login + relasi wali kelas
        $pengguna = [
            'nama'     => 'Siti Rahmawati, S.Pd.',
            'peran'    => 'Wali Kelas · X-B',
            'inisial'  => 'SR',
        ];

        // TODO (backend): ambil kelas yang diampu wali kelas login
        $kelas = 'X-B';

        // TODO (backend): ambil dari tabel tahun ajaran / semester
        $tahunAjaran       = '2026/2027 · Ganjil';
        $daftarTahunAjaran = ['2026/2027 · Ganjil', '2025/2026 · Genap', '2025/2026 · Ganjil'];

        $tanggalHariIni = now()->locale('id')->isoFormat('dddd, D MMMM YYYY');

        // TODO (backend): ambil dari tabel notifikasi milik user
        $notifikasi = [
            ['judul' => 'Nilai Matematika dikirim',           'keterangan' => 'Siti Rahmawati · STS Ganjil', 'waktu' => '2 jam lalu', 'baru' => true],
            ['judul' => 'Nilai Bahasa Inggris dikirim',       'keterangan' => 'Agus Prasetyo · STS Ganjil',  'waktu' => '1 hari lalu', 'baru' => true],
            ['judul' => 'Nilai Pendidikan Pancasila dikirim', 'keterangan' => 'Hadi Santoso · STS Ganjil',   'waktu' => '2 hari lalu', 'baru' => false],
            ['judul' => 'Periode STS dibuka',                 'keterangan' => 'Ditutup Jumat, 23 Oktober 2026', 'waktu' => '5 hari lalu', 'baru' => false],
        ];

        // TODO (backend): ambil mapel + pengampu + progres nilai rombel ini.
        // status: menunggu | disetujui | sedang_diisi | belum_tp | dikembalikan
        $mapel = [
            ['nama' => 'Matematika',          'kelompok' => 'Wajib', 'catatan' => null, 'guru' => 'Siti Rahmawati, S.Pd.', 'tp' => 6, 'sts' => 4, 'terisi' => 32, 'total' => 32, 'status' => 'menunggu',     'diperbarui' => '12 Okt'],
            ['nama' => 'Bahasa Indonesia',    'kelompok' => 'Wajib', 'catatan' => null, 'guru' => 'Dra. Endang Lestari',   'tp' => 5, 'sts' => 3, 'terisi' => 32, 'total' => 32, 'status' => 'disetujui',    'diperbarui' => '10 Okt'],
            ['nama' => 'Bahasa Inggris',      'kelompok' => 'Wajib', 'catatan' => null, 'guru' => 'Agus Prasetyo, S.Pd.',  'tp' => 6, 'sts' => 4, 'terisi' => 32, 'total' => 32, 'status' => 'menunggu',     'diperbarui' => '12 Okt'],
            ['nama' => 'Fisika',              'kelompok' => 'Wajib', 'catatan' => null, 'guru' => 'Rudi Hartono, M.Pd.',   'tp' => 5, 'sts' => 3, 'terisi' => 32, 'total' => 32, 'status' => 'disetujui',    'diperbarui' => '9 Okt'],
            ['nama' => 'Biologi',             'kelompok' => 'Wajib', 'catatan' => null, 'guru' => 'Nur Aini, S.Si.',       'tp' => 6, 'sts' => 4, 'terisi' => 27, 'total' => 32, 'status' => 'sedang_diisi', 'diperbarui' => '12 Okt'],
            ['nama' => 'Kimia',               'kelompok' => 'Wajib', 'catatan' => null, 'guru' => 'Bambang Sutrisno, S.Pd.', 'tp' => 5, 'sts' => 3, 'terisi' => 32, 'total' => 32, 'status' => 'dikembalikan', 'diperbarui' => '11 Okt'],
            ['nama' => 'Sejarah',             'kelompok' => 'Wajib', 'catatan' => null, 'guru' => 'Ika Puspitasari, S.Pd.', 'tp' => 4, 'sts' => 2, 'terisi' => 32, 'total' => 32, 'status' => 'disetujui',    'diperbarui' => '8 Okt'],
            ['nama' => 'Pendidikan Pancasila', 'kelompok' => 'Wajib', 'catatan' => null, 'guru' => 'Hadi Santoso, S.Pd.',  'tp' => 4, 'sts' => 2, 'terisi' => 32, 'total' => 32, 'status' => 'menunggu',     'diperbarui' => '11 Okt'],
            ['nama' => 'Informatika',         'kelompok' => 'Wajib', 'catatan' => null, 'guru' => 'Dimas Aditya, S.Kom.',  'tp' => 0, 'sts' => 0, 'terisi' => 0,  'total' => 32, 'status' => 'belum_tp',     'diperbarui' => null],
            ['nama' => 'Pend. Agama Islam dan Budi Pekerti', 'kelompok' => 'Agama · rombel campuran', 'catatan' => 'hanya siswa X-B ditampilkan', 'guru' => 'Ahmad Fauzi, S.Ag.', 'tp' => 5, 'sts' => 3, 'terisi' => 28, 'total' => 28, 'status' => 'disetujui', 'diperbarui' => '7 Okt'],
            ['nama' => 'Bahasa Mandarin',     'kelompok' => 'Bahasa Asing · Mandarin 1', 'catatan' => 'hanya siswa X-B ditampilkan', 'guru' => 'Lin Mei, S.Pd.', 'tp' => 6, 'sts' => 4, 'terisi' => 14, 'total' => 14, 'status' => 'disetujui', 'diperbarui' => '9 Okt'],
            ['nama' => 'Geografi',            'kelompok' => 'Wajib', 'catatan' => null, 'guru' => 'Wahyu Nugroho, S.Pd.',  'tp' => 4, 'sts' => 2, 'terisi' => 32, 'total' => 32, 'status' => 'disetujui',    'diperbarui' => '8 Okt'],
            ['nama' => 'Ekonomi',             'kelompok' => 'Wajib', 'catatan' => null, 'guru' => 'Ratna Dewi, S.E., M.Pd.', 'tp' => 4, 'sts' => 2, 'terisi' => 32, 'total' => 32, 'status' => 'disetujui',  'diperbarui' => '9 Okt'],
            ['nama' => 'PJOK',                'kelompok' => 'Wajib', 'catatan' => null, 'guru' => 'Yusuf Hidayat, S.Pd.',  'tp' => 4, 'sts' => 2, 'terisi' => 32, 'total' => 32, 'status' => 'disetujui',    'diperbarui' => '10 Okt'],
            ['nama' => 'Pend. Agama Kristen dan Budi Pekerti', 'kelompok' => 'Agama · rombel campuran', 'catatan' => 'hanya siswa X-B ditampilkan', 'guru' => 'Maria Simanjuntak, S.Th.', 'tp' => 5, 'sts' => 3, 'terisi' => 4, 'total' => 4, 'status' => 'disetujui', 'diperbarui' => '7 Okt'],
            ['nama' => 'Bahasa Jepang',       'kelompok' => 'Bahasa Asing · Jepang 1', 'catatan' => 'hanya siswa X-B ditampilkan', 'guru' => 'Dewi Anggraini, S.Pd.', 'tp' => 6, 'sts' => 4, 'terisi' => 12, 'total' => 18, 'status' => 'sedang_diisi', 'diperbarui' => '11 Okt'],
        ];

        $hitung = fn (string $status) => count(array_filter($mapel, fn ($m) => $m['status'] === $status));

        $totalMapel     = count($mapel);
        $jumlahMenunggu = $hitung('menunggu');
        $jumlahSetuju   = $hitung('disetujui');
        $mapelDikirim   = $jumlahMenunggu + $jumlahSetuju;

        // TODO (backend): ambil dari tabel siswa rombel + catatan wali
        $siswa = ['total' => 32, 'laki' => 16, 'perempuan' => 16];
        $catatanWaliTerisi = 26;
        $ekskulTerisi      = 30;
        $kehadiranTersedia = true;

        // TODO (backend): ambil dari tabel periode penilaian
        $periode = [
            'nama'  => 'Periode STS dibuka',
            'tutup' => 'Jumat, 23 Oktober 2026',
        ];

        $stats = [
            'siswa'          => $siswa,
            'mapel_dikirim'  => $mapelDikirim,
            'mapel_total'    => $totalMapel,
            'menunggu'       => $jumlahMenunggu,
            'pelengkap'      => $catatanWaliTerisi,
        ];

        // state: selesai | aktif | berjalan | menunggu
        $langkah = [
            ['judul' => 'Nilai dikirim guru',      'keterangan' => "{$mapelDikirim}/{$totalMapel} mapel", 'state' => 'selesai'],
            ['judul' => 'Diverifikasi wali kelas', 'keterangan' => "{$jumlahSetuju} disetujui",           'state' => 'aktif'],
            ['judul' => 'Pelengkap rapor',         'keterangan' => "{$catatanWaliTerisi}/{$siswa['total']} siswa", 'state' => 'berjalan'],
            ['judul' => 'Dikunci Kurikulum',       'keterangan' => 'Menunggu',                             'state' => 'menunggu'],
            ['judul' => 'Rapor siap dicetak',      'keterangan' => 'Belum tersedia',                       'state' => 'menunggu'],
        ];

        $linkVerifikasi = $this->link('verifikasi-nilai.index');
        $linkPelengkap  = $this->link('pelengkap-rapor.index');

        // Perlu tindakan, disusun dari data mapel di atas.
        // tone: verify | warn | note
        $perluTindakan = [];
        foreach ($mapel as $m) {
            if ($m['status'] === 'menunggu') {
                $perluTindakan[] = [
                    'tone'       => 'verify',
                    'judul'      => "Periksa nilai {$m['nama']}",
                    'keterangan' => "Dikirim {$m['diperbarui']} oleh " . strtok($m['guru'], ','),
                    'aksi'       => 'Periksa',
                    'url'        => $linkVerifikasi,
                ];
            }
        }
        foreach ($mapel as $m) {
            if ($m['status'] === 'belum_tp') {
                $perluTindakan[] = [
                    'tone'       => 'warn',
                    'judul'      => "{$m['nama']} belum memiliki TP",
                    'keterangan' => 'Guru: ' . strtok($m['guru'], ','),
                    'aksi'       => null,
                    'url'        => null,
                ];
            }
        }
        $belumCatatan = $siswa['total'] - $catatanWaliTerisi;
        if ($belumCatatan > 0) {
            $perluTindakan[] = [
                'tone'       => 'note',
                'judul'      => "{$belumCatatan} siswa belum ada catatan wali",
                'keterangan' => 'Pelengkap rapor STS',
                'aksi'       => 'Lengkapi',
                'url'        => $linkPelengkap,
            ];
        }

        $pelengkap = [
            'catatan'   => ['terisi' => $catatanWaliTerisi, 'total' => $siswa['total']],
            'kehadiran' => $kehadiranTersedia,
            'ekskul'    => ['terisi' => $ekskulTerisi, 'total' => $siswa['total']],
        ];

        // TODO (backend): true bila semua mapel disetujui & dikunci Kurikulum
        $statusRapor = [
            'bisa_cetak' => false,
            'judul'      => 'Rapor belum dapat dicetak',
            'keterangan' => 'Menunggu seluruh mapel disetujui dan dikunci oleh Kurikulum.',
        ];

        return view('dashboard-kelas.index', compact(
            'pengguna', 'kelas', 'tahunAjaran', 'daftarTahunAjaran', 'tanggalHariIni', 'notifikasi',
            'mapel', 'periode', 'stats', 'langkah', 'perluTindakan', 'pelengkap', 'statusRapor',
            'linkVerifikasi', 'linkPelengkap'
        ));
    }

    /** route() bila route sudah terdaftar, '#' bila belum. */
    private function link(string $name): string
    {
        return Route::has($name) ? route($name) : '#';
    }
}
