<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Support\Dummy\Kurikulum;
use App\Support\Dummy\Operator;
use App\Support\Dummy\Rapor;
use Illuminate\Http\Request;

/** Halaman rapor Admin Kurikulum (desain 7.01–7.05). */
class RaporController extends Controller
{
    /** 7.01 Format Rapor. */
    public function format()
    {
        return view('kurikulum.format-rapor', [
            'pengaturan' => Rapor::pengaturan(),
            'contoh' => ['sts' => Rapor::contoh('sts'), 'semester' => Rapor::contoh('semester')],
        ]);
    }

    /** 7.02 Cetak & Ekspor Rapor. Varian: ?pilih-kelas=1 (dropdown kelas terbuka). */
    public function cetak(Request $request)
    {
        $tingkat = 'X';
        $kelas = Kurikulum::statusKelas()->where('tingkat', $tingkat)->values();
        $dipilih = $kelas->firstWhere('kelas', $request->query('kelas'))['kelas'] ?? 'X-B';
        $data = Rapor::kelasCetak($dipilih);

        return view('kurikulum.cetak-rapor', [
            'tingkat' => $tingkat,
            'daftarKelas' => $kelas->map(fn ($k) => ['kelas' => $k['kelas'], 'kunci' => $k['kunci']])->all(),
            'ringkasan' => [
                'terkunci' => $kelas->where('kunci', 'terkunci')->count(),
                'pdf' => $kelas->where('rapor', 'pdf')->count(),
                'terbit' => 0,
            ],
            'data' => $data,
            'dokumen' => collect($data['siswa'])->mapWithKeys(fn ($s) => [$s['nis'] => [
                'sts' => Rapor::dokumen($s, 'sts'),
                'semester' => Rapor::dokumen($s, 'semester'),
            ]])->all(),
        ]);
    }

    /** 7.03 Terbitkan Rapor. Varian: ?modal=terbitkan (7.04). */
    public function terbitkan()
    {
        return view('kurikulum.terbitkan-rapor', ['kelas' => Rapor::penerbitan()]);
    }

    /** 7.05 Arsip & Versi Rapor. */
    public function arsip()
    {
        $arsip = Rapor::arsip();

        return view('kurikulum.arsip-rapor', [
            'arsip' => $arsip,
            'kelas' => collect(Operator::TINGKAT)->flatMap(fn ($t) => array_map(fn ($k) => "$t-$k", Operator::KELAS))->all(),
            'dokumen' => collect($arsip)->mapWithKeys(fn ($r) => [$r['id'] => Rapor::dokumen($r, $r['jenis'], $r['versi'][0]['v'])])->all(),
        ]);
    }
}
