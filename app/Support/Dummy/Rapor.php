<?php

namespace App\Support\Dummy;

/**
 * Data dummy dokumen rapor (desain Kurikulum 7.01–7.05, dipakai juga Wali Kelas & Siswa).
 * Bentuk `dokumen()` = isi snapshot rapor yang akan dirender komponen x-rapor.dokumen.
 */
class Rapor
{
    /** TODO API: GET /api/kurikulum/format-rapor */
    public static function pengaturan(): array
    {
        return [
            'logo' => 'logo_sman1.png',
            'logo_info' => 'PNG · 512×512 px',
            'baris1' => 'PEMERINTAH PROVINSI JAWA TIMUR',
            'baris2' => 'DINAS PENDIDIKAN',
            'sekolah' => 'SMA NEGERI 1 KOTA MALANG',
            'alamat' => 'Jl. Tugu Utara No. 1, Kota Malang, Jawa Timur',
            'kontak' => '(0341) 366454 · sman1malang.sch.id',
            'kepsek' => 'Dr. Hendra Prasetyo, M.Pd.',
            'nip_kepsek' => '197205141998021003',
            'tempat' => 'Malang',
            'tanggal_sts' => '2026-10-30',
            'tanggal_semester' => '2026-12-18',
            'komponen' => ['capaian' => true, 'kokurikuler' => true, 'ekskul' => true, 'absen' => true, 'catatan' => true, 'qr' => false],
        ];
    }

    /** Capaian kompetensi contoh per mapel (rapor semester). */
    private const CAPAIAN = [
        'Pendidikan Agama Islam dan Budi Pekerti' => 'Menunjukkan penguasaan yang sangat baik dalam memahami makna iman kepada malaikat. Perlu penguatan dalam menerapkan ketentuan zakat.',
        'Pendidikan Pancasila' => 'Menunjukkan penguasaan yang baik dalam menganalisis penerapan nilai Pancasila. Perlu penguatan dalam mengidentifikasi kasus pelanggaran hak warga negara.',
        'Bahasa Indonesia' => 'Menunjukkan penguasaan yang sangat baik dalam menyusun teks laporan hasil observasi. Perlu penguatan dalam menyunting teks negosiasi.',
        'Matematika' => 'Menunjukkan penguasaan yang baik dalam menyelesaikan sistem persamaan linear tiga variabel. Perlu penguatan dalam pemodelan fungsi kuadrat.',
        'Bahasa Inggris' => 'Menunjukkan penguasaan yang baik dalam memahami teks deskriptif. Perlu penguatan dalam menyusun teks prosedur secara runtut.',
        'Fisika' => 'Menunjukkan penguasaan yang baik dalam menerapkan konsep besaran dan pengukuran. Perlu penguatan dalam menganalisis gerak lurus berubah beraturan.',
        'Biologi' => 'Menunjukkan penguasaan yang baik dalam mengklasifikasikan keanekaragaman makhluk hidup. Perlu penguatan dalam menjelaskan peran virus.',
        'Kimia' => 'Menunjukkan penguasaan yang baik dalam menjelaskan struktur atom. Perlu penguatan dalam menyetarakan persamaan reaksi.',
        'Sejarah' => 'Menunjukkan penguasaan yang sangat baik dalam menjelaskan konsep dasar ilmu sejarah. Perlu penguatan dalam menganalisis sumber sejarah primer.',
    ];

    /** Nilai contoh Citra Lestari X-B (desain 7.01/7.02). */
    private const NILAI_CONTOH = [
        ['Pendidikan Agama Islam dan Budi Pekerti', [88, 86, 90, 87], 88],
        ['Pendidikan Pancasila', [85, 84, 86, 85], 85],
        ['Bahasa Indonesia', [90, 88, 91, 89], 90],
        ['Matematika', [76, 71, 72, 83], 76],
        ['Bahasa Inggris', [84, 83, 86, 82], 84],
        ['Fisika', [82, 80, 84, 81], 82],
        ['Kimia', [81, 79, 83, 80], 81],
        ['Sejarah', [89, 88, 90, 88], 89],
        ['Biologi', [87, 85, 88, 86], 87],
    ];

    /**
     * Dokumen rapor satu siswa. TODO API: GET /api/rapor/{id}/snapshot
     *
     * @param  array{nama:string,nis:string,nisn:string,kelas:string}  $siswa
     */
    public static function dokumen(array $siswa, string $jenis = 'sts', int $versi = 1): array
    {
        $p = self::pengaturan();
        $wali = WaliKelas::waliPerKelas()[$siswa['kelas']] ?? 'Siti Rahmawati, S.Pd.';
        // Citra Lestari = data contoh desain (nilai persis); siswa lain digeser sedikit agar bervariasi.
        $geser = $siswa['nama'] === 'Citra Lestari' ? 0 : crc32($siswa['nama']) % 7 - 3;

        $mapel = array_map(function ($m) use ($geser, $jenis) {
            [$nama, $tp, $akhir] = $m;
            $tp = array_map(fn ($n) => max(60, min(98, $n + $geser)), $tp);
            $akhir = max(60, min(98, $akhir + $geser + ($jenis === 'semester' ? -1 : 0)));

            return ['nama' => $nama, 'tp' => $tp, 'akhir' => $akhir, 'capaian' => self::CAPAIAN[$nama] ?? ''];
        }, $jenis === 'sts' ? array_slice(self::NILAI_CONTOH, 0, 8) : self::NILAI_CONTOH);

        return [
            'jenis' => $jenis,
            'versi' => $versi,
            'kop' => collect($p)->only(['baris1', 'baris2', 'sekolah', 'alamat', 'kontak'])->all(),
            'tahun_ajaran' => '2026/2027',
            'semester' => 'Ganjil',
            'siswa' => [
                'nama' => $siswa['nama'], 'nis' => $siswa['nis'], 'nisn' => $siswa['nisn'],
                'kelas' => $siswa['kelas'], 'fase' => str_starts_with($siswa['kelas'], 'X-') ? 'E' : 'F', 'sekolah' => 'SMA Negeri 1 Kota Malang',
            ],
            'mapel' => $mapel,
            'kokurikuler' => 'Projek "Gaya Hidup Berkelanjutan": aktif mengusulkan solusi pengurangan sampah plastik di sekolah.',
            'ekskul' => [['nama' => 'Pramuka', 'predikat' => 'B', 'ket' => 'Aktif mengikuti latihan rutin'], ['nama' => 'Paduan Suara', 'predikat' => 'SB', 'ket' => 'Tampil pada upacara HUT RI']],
            'absen' => ['sakit' => 1, 'izin' => 2, 'alpa' => 0],
            'catatan' => 'Pertahankan semangat belajar dan tingkatkan ketelitian dalam mengerjakan soal Matematika.',
            'ttd' => [
                'tempat' => $p['tempat'], 'tanggal' => $jenis === 'sts' ? $p['tanggal_sts'] : $p['tanggal_semester'],
                'wali' => $wali, 'kepsek' => $p['kepsek'], 'nip_kepsek' => $p['nip_kepsek'],
            ],
            'komponen' => $p['komponen'],
        ];
    }

    public static function contoh(string $jenis = 'sts'): array
    {
        return self::dokumen(['nama' => 'Citra Lestari', 'nis' => '2526003', 'nisn' => '0081234567', 'kelas' => 'X-B'], $jenis);
    }

    /** Daftar rapor satu kelas untuk Cetak Rapor. TODO API: GET /api/rapor?kelas=&jenis= */
    public static function kelasCetak(string $kelas): array
    {
        $terkunci = Kurikulum::statusKelas()->firstWhere('kelas', $kelas)['kunci'] ?? 'terbuka';
        $anggota = Operator::siswa()->where('kelas', $kelas)->values();

        return [
            'kelas' => $kelas,
            'terkunci' => $terkunci === 'terkunci',
            'dikunci' => $terkunci === 'terkunci' ? '26 Okt 2026' : null,
            'siswa' => $anggota->map(fn ($s) => [
                'nis' => $s['nis'], 'nisn' => $s['nisn'], 'nama' => $s['nama'], 'kelas' => $s['kelas'],
                'pdf' => $terkunci === 'terkunci' ? ($s['nama'] === 'Dimas Saputra' ? 'PDF v2 · 30 Okt' : 'PDF v1 · 26 Okt') : null,
            ])->all(),
        ];
    }

    /** Status penerbitan per kelas (desain 7.03). TODO API: GET /api/rapor/penerbitan?jenis=&tingkat= */
    public static function penerbitan(): array
    {
        return Kurikulum::statusKelas()->map(fn ($k) => [
            'kelas' => $k['kelas'],
            'tingkat' => $k['tingkat'],
            'wali' => $k['wali'],
            'kunci' => $k['kunci'],
            'pdf' => $k['kunci'] === 'terkunci',
            'jumlah' => $k['jumlah_siswa'],
            'terbit' => $k['kelas'] === 'X-B' ? '30 Okt 2026, 09.00' : null,
            'tanpa_ortu' => $k['kelas'] === 'X-B' ? ['Citra Dewi'] : ($k['kelas'] === 'X-G' ? ['Eko Hidayat'] : []),
        ])->values()->all();
    }

    /** Arsip versi rapor (desain 7.05). TODO API: GET /api/rapor/arsip?tahun=&semester=&jenis=&kelas= */
    public static function arsip(): array
    {
        $baris = [];
        foreach (Operator::siswa()->where('kelas', 'X-B')->values() as $i => $s) {
            $koreksi = $s['nama'] === 'Dimas Saputra';
            $versi = [['v' => 1, 'label' => 'v1', 'waktu' => '26 Okt 2026, 14.05', 'ket' => 'Versi awal setelah nilai dikunci · Sistem', 'status' => $koreksi ? 'arsip' : 'terbit']];
            if ($koreksi) {
                array_unshift($versi, ['v' => 2, 'label' => 'v2 · Koreksi', 'waktu' => '30 Okt 2026, 09.00', 'ket' => 'Koreksi nilai TP3 Matematika (8 → 80) · Dra. Wahyuni, M.Pd.', 'status' => 'terbit']);
            }
            $baris[] = [
                'id' => "xb-{$s['nis']}", 'nama' => $s['nama'], 'nis' => $s['nis'], 'nisn' => $s['nisn'], 'kelas' => 'X-B',
                'jenis' => 'sts', 'versi' => $versi, 'dibuat' => $koreksi ? '30 Okt 2026' : '26 Okt 2026',
                'oleh' => $koreksi ? 'Dra. Wahyuni, M.Pd.' : 'Sistem (kunci nilai)', 'alasan' => $koreksi ? 'Koreksi nilai TP3 Matematika' : null,
                'status' => 'terbit',
            ];
        }
        // Urutan halaman pertama mengikuti desain 7.05.
        $urut = ['Adinda Putri', 'Bagas Pratama', 'Citra Lestari', 'Dimas Saputra', 'Eka Wulandari'];
        usort($baris, fn ($a, $b) => (array_search($a['nama'], $urut, true) === false ? 99 : array_search($a['nama'], $urut, true)) <=> (array_search($b['nama'], $urut, true) === false ? 99 : array_search($b['nama'], $urut, true)));
        array_splice($baris, 5, 0, [[
            'id' => 'xa-2526017-sem', 'nama' => 'Ahmad Rizky', 'nis' => '2526017', 'nisn' => '0081230017', 'kelas' => 'X-A', 'jenis' => 'semester',
            'versi' => [['v' => 1, 'label' => 'v1', 'waktu' => '22 Jun 2026, 10.30', 'ket' => 'Rapor Semester Genap 2025/2026 · Sistem', 'status' => 'arsip']],
            'dibuat' => '22 Jun 2026', 'oleh' => 'Sistem (kunci nilai)', 'alasan' => null, 'status' => 'arsip',
        ]]);

        return $baris;
    }
}
