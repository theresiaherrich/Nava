<?php

namespace App\Http\Controllers;

use App\Support\Dummy\Kurikulum;
use App\Support\Dummy\Operator;
use App\Support\Dummy\Paginasi;
use App\Support\Dummy\Rombel;
use App\Support\Dummy\WaliKelas;
use Illuminate\Http\Request;

class DashboardKurikulumController extends Controller
{
    /** Dashboard Admin Kurikulum (desain 3.01). TODO API: GET /api/kurikulum/dashboard */
    public function index(Request $request)
    {
        $semuaKelas = Kurikulum::statusKelas();
        $tingkat = in_array($request->query('tingkat'), Operator::TINGKAT, true) ? $request->query('tingkat') : 'X';

        $kelas = $semuaKelas->where('tingkat', $tingkat)
            ->filter(fn ($k) => Paginasi::cocok($request->query('q'), $k['kelas'], (string) $k['wali']))
            ->values();

        $rombel = collect(Rombel::semua());
        $guru = Operator::guru();
        $tanpaWali = collect(WaliKelas::kelas())->whereNull('wali')->pluck('kelas');
        $statusPeriode = $semuaKelas->map(fn ($k) => Kurikulum::statusPeriode($k))->countBy();

        return view('dashboard-kurikulum.index', [
            'sapaan' => 'Bu Wahyuni',
            'hariIni' => 'Senin, 12 Oktober 2026',
            'statistik' => [
                'siswa' => Operator::siswa()->count(),
                'kelas' => $semuaKelas->count(),
                'guru' => $guru->count(),
                'asn' => $guru->where('status', 'ASN')->count(),
                'gtt' => $guru->where('status', 'GTT')->count(),
                'tanpa_pengampu' => $rombel->whereNull('guru')->count(),
                'tanpa_wali' => $tanpaWali->count(),
                'kelas_tanpa_wali' => $tanpaWali->implode(', '),
            ],
            'progres' => [
                ['tingkat' => 'Kelas X', 'terisi' => 82, 'terverifikasi' => 64, 'terkunci' => 40],
                ['tingkat' => 'Kelas XI', 'terisi' => 76, 'terverifikasi' => 58, 'terkunci' => 30],
                ['tingkat' => 'Kelas XII', 'terisi' => 88, 'terverifikasi' => 70, 'terkunci' => 50],
            ],
            'tingkat' => $tingkat,
            'kelas' => Paginasi::buat($kelas, $request),
            'tindakLanjut' => [
                ['ikon' => 'layers', 'tone' => 'warning', 'teks' => $rombel->whereNull('guru')->count().' rombel belum ada pengampu', 'aksi' => 'Atur', 'url' => route('rombel.index')],
                ['ikon' => 'user', 'tone' => 'warning', 'teks' => 'Kelas '.$tanpaWali->implode(', ').' belum ada wali kelas', 'aksi' => 'Tetapkan', 'url' => route('wali-kelas.index', ['tingkat' => 'XI'])],
                ['ikon' => 'undo', 'tone' => 'violet', 'teks' => '2 permintaan koreksi nilai', 'aksi' => 'Tinjau', 'url' => route('periode-penguncian.index')],
                ['ikon' => 'target', 'tone' => 'neutral', 'teks' => 'Informatika X-C belum ada TP', 'aksi' => null, 'ket' => 'menunggu guru'],
            ],
            'rapor' => [
                'siap' => $statusPeriode['siap'] ?? 0,
                'terkunci' => $statusPeriode['terkunci'] ?? 0,
                'terbit' => 0,
                'belum' => $semuaKelas->count() - ($statusPeriode['siap'] ?? 0) - ($statusPeriode['terkunci'] ?? 0),
                'total' => $semuaKelas->count(),
            ],
        ]);
    }
}
