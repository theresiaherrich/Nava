<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AkademikNilaiController extends PortalBaseController
{
    public function index(Request $request)
    {
        // Ambil data nilai siswa
        $nilai = collect($this->dataNilai());

        // Filter pencarian mata pelajaran
        $q = trim((string) $request->query('q', ''));

        if ($q !== '') {
            $nilai = $nilai
                ->filter(fn ($n) => mb_stripos(
                    (string) ($n->mapel ?? ''),
                    $q
                ) !== false)
                ->values();
        }

        // Hanya gunakan nilai numerik untuk perhitungan
        $nilaiBernilai = $nilai->filter(
            fn ($n) => is_numeric($n->nilai ?? null)
        );

        // Rata-rata nilai
        $rata = $nilaiBernilai->isNotEmpty()
            ? (float) $nilaiBernilai->avg('nilai')
            : 0;

        // Nilai tertinggi dan terendah
        $tertinggi = $nilaiBernilai->sortByDesc('nilai')->first();
        $terendah = $nilaiBernilai->sortBy('nilai')->first();

        // Riwayat kelas aktif
        $rk = $this->riwayatAktif();

        // Informasi rapor
        $rapor = $rk
            ? DB::table('rapor as rp')
                ->join(
                    'periode_nilai as pn',
                    'pn.id_periode',
                    '=',
                    'rp.id_periode'
                )
                ->where('rp.id_riwayat_kelas', $rk->id_riwayat_kelas)
                ->orderByDesc('rp.waktu_terbit')
                ->first()
            : null;

        // Informasi stabilitas belajar
        $total = $nilaiBernilai->count();

        $optimal = $nilaiBernilai
            ->filter(fn ($n) => $n->nilai >= self::NILAI_OPTIMAL)
            ->count();

        $persen = $total > 0
            ? (int) round(($optimal / $total) * 100)
            : 0;

        $stabilitas = (object) [
            'total' => $total,
            'optimal' => $optimal,
            'persen' => $persen,
            'predikat' => $total > 0
                ? $this->predikat($rata)
                : '—',
            'ambang' => self::NILAI_OPTIMAL,
        ];

        // Informasi pengesahan rapor
        $disahkanPada = $rapor && $rapor->waktu_terbit
            ? \Carbon\Carbon::parse($rapor->waktu_terbit)
            : null;

        return view('portal-orang-tua.akademik-nilai', [
            'siswa' => $this->dataSiswa(),
            'ortu' => $this->dataOrtu(),

            'nilai' => $nilai,
            'rataRata' => $rata,

            // Belum ada perbandingan semester sebelumnya
            'selisih' => null,

            'semesterLabel' => $rk
                ? ucfirst(strtolower($rk->semester_nama))
                    . ' ' . $rk->tahun_ajaran
                : 'Semester belum tersedia',

            'periodeLabel' => $rapor
                ? ($rapor->jenis . ' · ' . $rapor->status)
                : 'Periode rapor belum tersedia',

            'diverifikasi' => $rapor
                && in_array($rapor->status, ['siap', 'terbit']),

            'disahkanPada' => $disahkanPada,

            'catatan' => $this->dataCatatanSekolah(),

            'stabilitas' => $stabilitas,

            'tertinggi' => $tertinggi,
            'terendah' => $terendah,

            'portofolio' => [],
            'target' => null,

            'kktp' => self::KKTP,
            'q' => $q,
        ]);
    }

    public function unduh(): RedirectResponse
    {
        return back()->with('toast', [
            'type' => 'info',
            'message' => 'Unduhan rapor belum tersedia.',
        ]);
    }
}
