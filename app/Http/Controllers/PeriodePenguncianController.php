<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PeriodePenguncianController extends Controller
{
    private const JUDUL = [
        'STS' => 'Sumatif Tengah Semester (STS)',
        'SAS' => 'Sumatif Akhir Semester (SAS)',
    ];

    private const STATUS_LABEL = [
        'terjadwal' => 'Terjadwal',
        'dibuka'    => 'Dibuka',
        'ditutup'   => 'Ditutup',
    ];

    public function index(Request $request)
    {
        // TODO (backend): ganti semua data dummy di method ini dengan query database
        $hariIni = $this->hariIni($request);

        $semesterTabs = ['Semester Ganjil 2026/2027', 'Semester Genap 2026/2027'];
        $semesterAktif = (int) $request->query('semester', 0);
        if (! array_key_exists($semesterAktif, $semesterTabs)) {
            $semesterAktif = 0;
        }

        // status kelas: siap | terkunci | menunggu | koreksi | belum
        $tingkatGanjil = [
            'X' => [
                'kelas' => [
                    ['kode' => 'X-A', 'wali' => 'Andini Pratiwi, S.Pd.',    'terisi' => 16, 'total' => 16, 'status' => 'siap'],
                    ['kode' => 'X-B', 'wali' => 'Siti Rahmawati, S.Pd.',    'terisi' => 16, 'total' => 16, 'status' => 'terkunci', 'dikunci' => '26 Okt 2026'],
                    ['kode' => 'X-C', 'wali' => 'Bayu Nugraha, S.Pd.',      'terisi' => 14, 'total' => 16, 'status' => 'menunggu'],
                    ['kode' => 'X-D', 'wali' => 'Ratna Sari, M.Pd.',        'terisi' => 16, 'total' => 16, 'status' => 'terkunci', 'dikunci' => '25 Okt 2026'],
                    ['kode' => 'X-E', 'wali' => 'Yusuf Hidayat, S.Pd.',     'terisi' => 16, 'total' => 16, 'status' => 'koreksi',
                        'catatan' => 'Matematika · telah input TP3'],
                    ['kode' => 'X-F', 'wali' => 'Lestari Wulandari, S.Pd.', 'terisi' => 16, 'total' => 16, 'status' => 'siap'],
                ],
            ],
            'XI' => [
                'kelas' => [
                    ['kode' => 'XI-A', 'wali' => 'Dewi Kartika, S.Pd.', 'terisi' => 10, 'total' => 16, 'status' => 'menunggu'],
                    ['kode' => 'XI-B', 'wali' => 'Rudi Hartono, S.Pd.', 'terisi' => 16, 'total' => 16, 'status' => 'siap'],
                ],
            ],
            'XII' => [
                'kelas' => [
                    ['kode' => 'XII-A', 'wali' => 'Hendra Wijaya, M.Pd.', 'terisi' => 16, 'total' => 16, 'status' => 'terkunci', 'dikunci' => '24 Okt 2026'],
                ],
            ],
        ];

        // Semester Genap: periode belum dibuka, nilai belum diisi
        $tingkatGenap = [
            'X' => [
                'kelas' => [
                    ['kode' => 'X-A', 'wali' => 'Andini Pratiwi, S.Pd.',    'terisi' => 0, 'total' => 16, 'status' => 'belum'],
                    ['kode' => 'X-B', 'wali' => 'Siti Rahmawati, S.Pd.',    'terisi' => 0, 'total' => 16, 'status' => 'belum'],
                    ['kode' => 'X-C', 'wali' => 'Bayu Nugraha, S.Pd.',      'terisi' => 0, 'total' => 16, 'status' => 'belum'],
                    ['kode' => 'X-D', 'wali' => 'Ratna Sari, M.Pd.',        'terisi' => 0, 'total' => 16, 'status' => 'belum'],
                ],
            ],
            'XI' => [
                'kelas' => [
                    ['kode' => 'XI-A', 'wali' => 'Dewi Kartika, S.Pd.', 'terisi' => 0, 'total' => 16, 'status' => 'belum'],
                    ['kode' => 'XI-B', 'wali' => 'Rudi Hartono, S.Pd.', 'terisi' => 0, 'total' => 16, 'status' => 'belum'],
                    ['kode' => 'XI-C', 'wali' => 'Maya Anggraini, S.Pd.', 'terisi' => 0, 'total' => 16, 'status' => 'belum'],
                ],
            ],
            'XII' => [
                'kelas' => [
                    ['kode' => 'XII-A', 'wali' => 'Hendra Wijaya, M.Pd.', 'terisi' => 0, 'total' => 16, 'status' => 'belum'],
                    ['kode' => 'XII-B', 'wali' => 'Fajar Setiawan, S.Pd.', 'terisi' => 0, 'total' => 16, 'status' => 'belum'],
                ],
            ],
        ];

        // Satu periode = semester (index tab) + jenis + tingkat. Tanggal format Y-m-d.
        $periodeList = [
            ['id' => 1, 'semester' => 0, 'jenis' => 'STS', 'tingkat' => 'X',   'buka' => '2026-09-21', 'tutup' => '2026-10-23'],
            ['id' => 2, 'semester' => 0, 'jenis' => 'STS', 'tingkat' => 'XI',  'buka' => '2026-09-21', 'tutup' => '2026-10-23'],
            ['id' => 3, 'semester' => 0, 'jenis' => 'STS', 'tingkat' => 'XII', 'buka' => '2026-09-21', 'tutup' => '2026-10-23'],
            ['id' => 4, 'semester' => 0, 'jenis' => 'SAS', 'tingkat' => 'X',   'buka' => '2026-11-16', 'tutup' => '2026-12-18'],
            ['id' => 5, 'semester' => 0, 'jenis' => 'SAS', 'tingkat' => 'XI',  'buka' => '2026-11-16', 'tutup' => '2026-12-18'],
            ['id' => 6, 'semester' => 0, 'jenis' => 'SAS', 'tingkat' => 'XII', 'buka' => '2026-11-16', 'tutup' => '2026-12-18'],
            ['id' => 7, 'semester' => 1, 'jenis' => 'STS', 'tingkat' => 'X',   'buka' => '2027-03-15', 'tutup' => '2027-04-16'],
            ['id' => 8, 'semester' => 1, 'jenis' => 'STS', 'tingkat' => 'XI',  'buka' => '2027-03-15', 'tutup' => '2027-04-16'],
            ['id' => 9, 'semester' => 1, 'jenis' => 'STS', 'tingkat' => 'XII', 'buka' => '2027-02-22', 'tutup' => '2027-03-19'],
            ['id' => 10, 'semester' => 1, 'jenis' => 'SAS', 'tingkat' => 'X',  'buka' => '2027-06-01', 'tutup' => '2027-06-18'],
            ['id' => 11, 'semester' => 1, 'jenis' => 'SAS', 'tingkat' => 'XI', 'buka' => '2027-06-01', 'tutup' => '2027-06-18'],
            // Kelas XII sengaja tidak punya SAS Genap (diganti Ujian Sekolah)
        ];

        // Demo empty state: /periode-penguncian?genap_kosong=1
        if ($request->boolean('genap_kosong')) {
            $periodeList = array_values(array_filter($periodeList, fn ($p) => $p['semester'] !== 1));
        }

        $semesterData = [];
        foreach ([$tingkatGanjil, $tingkatGenap] as $i => $tingkatData) {
            $tingkatData = $this->hitungPersen($tingkatData);
            $tingkatAktif = $request->query('tingkat', 'X');
            if (! array_key_exists($tingkatAktif, $tingkatData)) {
                $tingkatAktif = array_key_first($tingkatData);
            }

            $semesterData[$i] = [
                'tingkatData'  => $tingkatData,
                'tingkatAktif' => $tingkatAktif,
                'adaPeriode'   => collect($periodeList)->contains('semester', $i),
                'sts'          => $this->ringkasan($periodeList, $i, 'STS', $tingkatAktif, $hariIni),
                'sas'          => $this->ringkasan($periodeList, $i, 'SAS', $tingkatAktif, $hariIni),
            ];
        }

        $aktif = $semesterData[$semesterAktif];

        return view('periode-penguncian.index', [
            'semesterTabs'  => $semesterTabs,
            'semesterAktif' => $semesterAktif,
            'sts'           => $aktif['sts'],
            'tingkatAktif'  => $aktif['tingkatAktif'],
            'tingkatData'   => $aktif['tingkatData'],
            'sas'           => $aktif['sas'],
            // Pilihan mapel di modal "Buka koreksi" (format: Mapel — Guru)
            'mapelList' => [
                'Matematika — Siti Rahmawati',
                'Bahasa Indonesia — Andini Pratiwi',
                'Bahasa Inggris — Bayu Nugraha',
                'Fisika — Ratna Sari',
                'Kimia — Yusuf Hidayat',
                'Biologi — Lestari Wulandari',
            ],
            // Struktur baru: data per semester + daftar periode untuk JS
            'semesterData' => $semesterData,
            'periodeList'  => $periodeList,
            'hariIni'      => $hariIni->toDateString(),
        ]);
    }

    /**
     * Tanggal acuan "hari ini". Bisa ditimpa untuk demo: ?hari_ini=2026-10-12
     */
    private function hariIni(Request $request): Carbon
    {
        $override = $request->query('hari_ini');
        if (is_string($override) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $override)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $override)->startOfDay();
            } catch (\Throwable) {
                // abaikan, pakai tanggal sekarang
            }
        }

        return now()->startOfDay();
    }

    private function hitungPersen(array $tingkatData): array
    {
        foreach ($tingkatData as $tingkat => $data) {
            foreach ($data['kelas'] as $i => $kelas) {
                $tingkatData[$tingkat]['kelas'][$i]['persen'] =
                    (int) round($kelas['terisi'] / $kelas['total'] * 100);
            }
            $tingkatData[$tingkat]['keseluruhan'] =
                (int) round(collect($tingkatData[$tingkat]['kelas'])->avg('persen'));
        }

        return $tingkatData;
    }

    /**
     * Ringkasan periode untuk tampilan awal kartu STS/SAS.
     * Logika yang sama ada di periode-penguncian.js (dipakai setelah halaman dimuat).
     */
    private function ringkasan(array $periodeList, int $semester, string $jenis, string $tingkat, Carbon $hariIni): array
    {
        $periode = collect($periodeList)->first(fn ($p) => $p['semester'] === $semester
            && $p['jenis'] === $jenis
            && $p['tingkat'] === $tingkat);

        if (! $periode) {
            return [
                'judul'            => self::JUDUL[$jenis],
                'ada'              => false,
                'tanggal'          => 'Belum dijadwalkan untuk Kelas ' . $tingkat,
                'status'           => null,
                'statusLabel'      => '-',
                'tanggalBuka'      => '–',
                'bulanBuka'        => '',
                'hariSampaiDibuka' => 0,
            ];
        }

        $buka  = Carbon::parse($periode['buka'])->locale('id');
        $tutup = Carbon::parse($periode['tutup'])->locale('id');

        $status = match (true) {
            $hariIni->lt($buka)  => 'terjadwal',
            $hariIni->gt($tutup) => 'ditutup',
            default              => 'dibuka',
        };

        $tanggal = $buka->year === $tutup->year
            ? $buka->translatedFormat('j F') . ' – ' . $tutup->translatedFormat('j F Y')
            : $buka->translatedFormat('j F Y') . ' – ' . $tutup->translatedFormat('j F Y');

        return [
            'judul'            => self::JUDUL[$jenis],
            'ada'              => true,
            'tanggal'          => $tanggal,
            'status'           => $status,
            'statusLabel'      => self::STATUS_LABEL[$status],
            'tanggalBuka'      => $buka->format('d'),
            'bulanBuka'        => strtoupper($buka->translatedFormat('M')),
            'hariSampaiDibuka' => max(0, (int) $hariIni->diffInDays($buka, false)),
        ];
    }
}
