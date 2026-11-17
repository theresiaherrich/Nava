<?php

namespace App\Support\Dummy;

use Illuminate\Support\Str;

/**
 * Data dummy portal Siswa (desain Siswa 2.01–7.01) untuk Citra Lestari, X-B.
 * Hanya nilai resmi yang sudah diterbitkan yang boleh tampil di portal siswa.
 * State: normal (STS terbit) | belum-terbit (belum ada rapor) | semester-terbit | versi-baru (STS v2 setelah koreksi).
 */
class Siswa
{
    public const KKTP = 75;

    /** TODO API: GET /api/siswa/me */
    public static function profil(): array
    {
        return [
            'nama' => 'Citra Lestari', 'panggilan' => 'Citra', 'nis' => '2526003', 'nisn' => '0081234567',
            'kelas' => 'X-B', 'fase' => 'E', 'wali' => 'Siti Rahmawati, S.Pd.', 'inisial' => 'CL',
        ];
    }

    /**
     * Nilai rapor STS per mapel: [mapel, kelompok, guru, nilai per TP STS (null = TP belum dinilai), nilai akhir].
     * Urutan 8 mapel pertama = urutan dokumen rapor STS (desain 3.01).
     */
    private const MAPEL = [
        ['Pend. Agama Islam dan Budi Pekerti', 'Agama', 'Ahmad Fauzi, S.Ag.', [90, 88, 89], 89],
        ['Pendidikan Pancasila', 'Wajib', 'Hadi Santoso, S.Pd.', [86, 84], 85],
        ['Bahasa Indonesia', 'Wajib', 'Dra. Endang Lestari', [90, 86, 88], 88],
        ['Matematika', 'Wajib', 'Siti Rahmawati, S.Pd.', [88, 92, 80, 85], 86],
        ['Bahasa Inggris', 'Wajib', 'Agus Prasetyo, S.Pd.', [92, 88, 90, 90], 90],
        ['Fisika', 'Wajib', 'Rudi Hartono, M.Pd.', [76, 75, 65], 72],
        ['Kimia', 'Wajib', 'Bambang Sutrisno, S.Pd.', [78, 68, 64], 70],
        ['Sejarah', 'Wajib', 'Ika Puspitasari, S.Pd.', [88, 86], 87],
        ['Biologi', 'Wajib', 'Nur Aini, S.Si.', [85, 83], 84],
        ['Bahasa Mandarin', 'Bahasa Asing', 'Lin Mei, S.Pd.', [92, 90, 91], 91],
        ['Geografi', 'Wajib', 'Wahyu Nugroho, S.Pd.', [88, 88], 88],
        ['Ekonomi', 'Wajib', 'Ratna Dewi, S.E., M.Pd.', [86, 88], 87],
        ['PJOK', 'Wajib', 'Yusuf Hidayat, S.Pd.', [92, 92], 92],
        ['Informatika', 'Wajib', 'Dimas Aditya, S.Kom.', [90, 90], 90],
        ['Seni Budaya', 'Wajib', 'Rina Marlina, S.Sn.', [91, 91], 91],
        ['Bahasa Jawa', 'Muatan Lokal', 'Sri Lestari, S.Pd.', [90, 92], 91],
    ];

    /** Deskripsi TP sesuai desain 4.02/4.03; mapel lain memakai deskripsi umum. */
    private const TP = [
        'Matematika' => ['Menjelaskan sifat-sifat bilangan berpangkat dan bentuk akar', 'Menerapkan barisan dan deret aritmetika dalam masalah kontekstual', 'Menyelesaikan sistem persamaan linear tiga variabel', 'Menganalisis pertidaksamaan linear dua variabel'],
        'Fisika' => ['Menerapkan konsep besaran dan pengukuran', 'Menganalisis vektor dan resultan gaya', 'Menganalisis gerak lurus berubah beraturan'],
        'Kimia' => ['Menjelaskan perkembangan model atom', 'Menganalisis konfigurasi elektron dan sistem periodik', 'Menyetarakan persamaan reaksi kimia'],
    ];

    private const DESKRIPSI = [
        'Matematika' => ['Menunjukkan penguasaan yang sangat baik dalam menerapkan barisan dan deret aritmetika dalam masalah kontekstual.', null],
        'Fisika' => ['Menunjukkan penguasaan yang baik dalam menerapkan konsep besaran dan pengukuran.', 'Perlu penguatan dalam menganalisis gerak lurus berubah beraturan.'],
        'Kimia' => ['Menunjukkan penguasaan yang baik dalam menjelaskan perkembangan model atom.', 'Perlu penguatan dalam menyetarakan persamaan reaksi kimia.'],
    ];

    /** Rapor yang tersedia untuk siswa. TODO API: GET /api/siswa/rapor */
    public static function rapor(string $state = 'normal'): array
    {
        $stsTerbit = $state !== 'belum-terbit';

        return [
            'sts' => [
                'jenis' => 'sts', 'label' => 'Rapor STS', 'periode' => 'Ganjil 2026/2027',
                'terbit' => $stsTerbit ? ($state === 'versi-baru' ? '2 Nov 2026' : '30 Okt 2026') : null,
                'jam' => '09.00', 'versi' => $state === 'versi-baru' ? 2 : 1,
                'jadwal' => 'Diterbitkan setelah nilai dikunci',
            ],
            'semester' => [
                'jenis' => 'semester', 'label' => 'Rapor Semester', 'periode' => 'Ganjil 2026/2027',
                'terbit' => $state === 'semester-terbit' ? '22 Des 2026' : null,
                'jam' => '10.00', 'versi' => 1,
                'jadwal' => 'Diterbitkan setelah 18 Des 2026',
            ],
        ];
    }

    /** Nilai per mapel dari rapor STS yang sudah terbit. TODO API: GET /api/siswa/nilai?periode=sts */
    public static function nilai(): array
    {
        return array_map(function ($m, $i) {
            [$mapel, $kelompok, $guru, $tp, $akhir] = $m;
            $teksTp = self::TP[$mapel] ?? array_map(fn ($j) => "Capaian pembelajaran {$mapel} bagian ".($j + 1), array_keys($tp));
            $deskripsi = self::DESKRIPSI[$mapel] ?? ["Menunjukkan penguasaan yang baik dalam seluruh tujuan pembelajaran {$mapel}.", null];

            return [
                'id' => Str::slug($mapel),
                'mapel' => $mapel, 'kelompok' => $kelompok, 'guru' => $guru, 'nilai' => $akhir,
                'tercapai' => $akhir >= self::KKTP,
                'tp' => array_map(fn ($n, $j) => ['kode' => 'TP'.($j + 1), 'deskripsi' => $teksTp[$j], 'nilai' => $n], $tp, array_keys($tp)),
                'deskripsi' => $deskripsi[0], 'penguatan' => $deskripsi[1],
                'urut_rapor' => $i,
            ];
        }, self::MAPEL, array_keys(self::MAPEL));
    }

    /** Dokumen rapor siswa (bentuk Rapor::dokumen) memakai nilai resmi portal siswa. */
    public static function dokumen(string $jenis): array
    {
        $p = self::profil();
        $doc = Rapor::dokumen(['nama' => $p['nama'], 'nis' => $p['nis'], 'nisn' => $p['nisn'], 'kelas' => $p['kelas']], $jenis);

        if ($jenis === 'sts') {
            $doc['mapel'] = array_map(fn ($m) => [
                'nama' => $m[0] === 'Pend. Agama Islam dan Budi Pekerti' ? 'Pendidikan Agama Islam dan Budi Pekerti' : $m[0],
                'tp' => array_pad($m[3], 4, null), 'akhir' => $m[4], 'capaian' => '',
            ], array_slice(self::MAPEL, 0, 8));
        } else {
            $doc['catatan'] = 'Ananda disiplin dan santun. Terus kembangkan bakat yang dimiliki.';
            $doc['absen'] = ['sakit' => 0, 'izin' => 1, 'alpa' => 0];
            $doc['ekskul'] = [['nama' => 'Pramuka', 'predikat' => 'SB', 'ket' => 'Sangat aktif dalam kegiatan perkemahan'], ['nama' => 'Paduan Suara', 'predikat' => 'B', 'ket' => 'Aktif dalam latihan dan pentas sekolah']];
        }

        return $doc;
    }

    /** Ringkasan beranda dari rapor STS terbit. */
    public static function ringkasan(): array
    {
        $nilai = collect(self::nilai());

        return [
            'rata' => round($nilai->avg('nilai'), 1),
            'jumlah' => $nilai->count(),
            'tercapai' => $nilai->where('tercapai', true)->count(),
            'penguatan' => $nilai->where('tercapai', false)->pluck('mapel')->all(),
        ];
    }
}
