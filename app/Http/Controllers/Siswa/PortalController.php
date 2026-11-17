<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Support\Dummy\Siswa;
use Illuminate\Http\Request;

/**
 * Portal Siswa (desain Siswa 2.01–7.01). Hanya menampilkan nilai/rapor resmi yang sudah diterbitkan.
 * Varian bersama: ?state=belum-terbit (2.05) | semester-terbit (3.05) | versi-baru (5.02).
 */
class PortalController extends Controller
{
    private function state(Request $request): string
    {
        return in_array($request->query('state'), ['belum-terbit', 'semester-terbit', 'versi-baru'], true) ? $request->query('state') : 'normal';
    }

    /** 2.01 / 2.05 / 7.01 Beranda Akademik. */
    public function beranda(Request $request)
    {
        $state = $this->state($request);
        $rapor = Siswa::rapor($state);
        $terbit = (bool) $rapor['sts']['terbit'];

        return view('siswa.beranda', [
            'profil' => Siswa::profil(),
            'rapor' => $rapor,
            'terbit' => $terbit,
            'hariIni' => $terbit ? 'Senin, 2 November 2026' : 'Senin, 12 Oktober 2026',
            'ringkasan' => $terbit ? Siswa::ringkasan() : null,
            'capaian' => $terbit ? collect(Siswa::nilai())->whereIn('mapel', ['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'Fisika', 'Biologi', 'Kimia'])
                ->sortBy(fn ($m) => array_search($m['mapel'], ['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'Fisika', 'Biologi', 'Kimia'], true))->values()->all() : [],
            'kktp' => Siswa::KKTP,
        ]);
    }

    /** 3.01 / 3.04 / 3.05 Rapor Saya. ?jenis=sts|semester */
    public function rapor(Request $request)
    {
        $rapor = Siswa::rapor($this->state($request));

        return view('siswa.rapor', [
            'rapor' => $rapor,
            'jenis' => $request->query('jenis') === 'semester' ? 'semester' : 'sts',
            'dokumen' => [
                'sts' => $rapor['sts']['terbit'] ? Siswa::dokumen('sts') : null,
                'semester' => $rapor['semester']['terbit'] ? Siswa::dokumen('semester') : null,
            ],
        ]);
    }

    /** 4.01 Nilai per Mapel. */
    public function nilai(Request $request)
    {
        $terbit = (bool) Siswa::rapor($this->state($request))['sts']['terbit'];
        $urut = ['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'Fisika', 'Biologi', 'Kimia', 'Sejarah', 'Bahasa Mandarin', 'Pend. Agama Islam dan Budi Pekerti'];

        return view('siswa.nilai', [
            'nilai' => $terbit ? collect(Siswa::nilai())->sortBy(fn ($m) => array_search($m['mapel'], $urut, true) === false ? 99 + $m['urut_rapor'] : array_search($m['mapel'], $urut, true))->values()->all() : [],
            'kktp' => Siswa::KKTP,
        ]);
    }

    /** 4.02 / 4.03 Detail nilai mapel. Mapel yang tidak ada di rapor terbit → 6.01 akses ditolak. */
    public function nilaiDetail(Request $request, string $mapel)
    {
        $terbit = (bool) Siswa::rapor($this->state($request))['sts']['terbit'];
        $m = $terbit ? collect(Siswa::nilai())->firstWhere('id', $mapel) : null;

        if (! $m) {
            return $this->aksesDitolak();
        }

        return view('siswa.nilai-detail', ['m' => $m, 'kktp' => Siswa::KKTP]);
    }

    /** 5.01 / 5.02 Riwayat Rapor. */
    public function riwayat(Request $request)
    {
        $state = $this->state($request);

        return view('siswa.riwayat', ['rapor' => Siswa::rapor($state), 'kelas' => Siswa::profil()['kelas'], 'diperbarui' => $state === 'versi-baru']);
    }

    /** 6.01 Akses ditolak (di dalam layout aplikasi, status 403). */
    public function aksesDitolak()
    {
        return response()->view('siswa.akses-ditolak', [], 403);
    }
}
