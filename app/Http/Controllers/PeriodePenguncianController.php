<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
 
class PeriodePenguncianController extends Controller
{
    public function index(Request $request)
    {
        // TODO (backend): ganti data dummy ini dengan query database
        $tanggalBukaSas = Carbon::parse('2026-11-16');
 
        // status: siap | terkunci | menunggu | koreksi
        $tingkatData = [
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
 
        // Hitung persen per kelas & keseluruhan per tingkat
        foreach ($tingkatData as $tingkat => $data) {
            foreach ($data['kelas'] as $i => $kelas) {
                $tingkatData[$tingkat]['kelas'][$i]['persen'] =
                    (int) round($kelas['terisi'] / $kelas['total'] * 100);
            }
            $tingkatData[$tingkat]['keseluruhan'] =
                (int) round(collect($tingkatData[$tingkat]['kelas'])->avg('persen'));
        }
 
        return view('periode-penguncian.index', [
            'semesterTabs'  => ['Semester Ganjil 2026/2027', 'Semester Genap 2026/2027'],
            'semesterAktif' => 0,
            'sts' => [
                'judul'       => 'Sumatif Tengah Semester (STS)',
                'tanggal'     => '21 September – 23 Oktober 2026',
                'statusLabel' => 'Dibuka',
            ],
            'tingkatAktif' => $request->query('tingkat', 'X'),
            'tingkatData'  => $tingkatData,
            'sas' => [
                'judul'            => 'Sumatif Akhir Semester (SAS)',
                'tanggal'          => '16 November – 18 Desember 2026',
                'statusLabel'      => 'Terjadwal',
                'tanggalBuka'      => $tanggalBukaSas->format('d'),
                'bulanBuka'        => 'NOV',
                'hariSampaiDibuka' => max(0, (int) now()->startOfDay()->diffInDays($tanggalBukaSas, false)),
            ],
            // Pilihan mapel di modal "Buka koreksi" (format: Mapel — Guru)
            'mapelList' => [
                'Matematika — Siti Rahmawati',
                'Bahasa Indonesia — Andini Pratiwi',
                'Bahasa Inggris — Bayu Nugraha',
                'Fisika — Ratna Sari',
                'Kimia — Yusuf Hidayat',
                'Biologi — Lestari Wulandari',
            ],
        ]);
    }
}
 