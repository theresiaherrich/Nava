<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DataKelasController extends Controller
{
    private const PER_PAGE = 10;

    public function index(Request $request)
    {
        $semester = (string) $request->query('semester', 'ganjil');
        $tingkat  = (string) $request->query('tingkat', 'X');
        $q        = trim((string) $request->query('q', ''));
        $page     = max(1, (int) $request->query('page', 1));

        $semuaKelas      = $this->semuaKelas();
        $anggotaPerKelas = $this->anggotaPerKelas();

        $kelasTingkat = $semuaKelas->where('tingkat', $tingkat)->values();

        $kodeDiminta = (string) $request->query('kelas', '');
        $kelasAktif  = $kelasTingkat->contains('kode', $kodeDiminta)
            ? $kodeDiminta
            : ($kelasTingkat->first()['kode'] ?? '');

        $infoKelas    = $kelasTingkat->firstWhere('kode', $kelasAktif);
        $semuaAnggota = collect($anggotaPerKelas->get($kelasAktif, []));
        $jumlahAktif  = $semuaAnggota->count();

        $anggota = $semuaAnggota
            ->when($q !== '', fn ($c) => $c->filter(fn ($s) =>
                str_contains(mb_strtolower($s['nama'] ?? ''), mb_strtolower($q)) ||
                str_contains((string) ($s['nis'] ?? ''), $q)
            ))
            ->sortBy(fn ($s) => mb_strtolower($s['nama'] ?? ''))
            ->values();

        $totalAnggota = $anggota->count();
        $lastPage     = max(1, (int) ceil($totalAnggota / self::PER_PAGE));
        $page         = min($page, $lastPage);
        $anggotaRows  = $anggota->forPage($page, self::PER_PAGE)->values();

        $opsiSemester = ['ganjil' => 'Semester: Ganjil 2026/2027'];
        $opsiTingkat  = [
            'X'   => 'Tingkat: Kelas X',
            'XI'  => 'Tingkat: Kelas XI',
            'XII' => 'Tingkat: Kelas XII',
        ];
        $opsiPindah = ['' => 'Pilih kelas tujuan'] + $semuaKelas
            ->mapWithKeys(fn ($k) => [$k['kode'] => 'Kelas ' . $k['kode'] . ' · Wali: ' . ($k['wali_kelas'] ?? '-')])
            ->all();

        return view('data-kelas.index', [
            'semester'          => $semester,
            'tingkat'           => $tingkat,
            'kelasAktif'        => $kelasAktif,
            'infoKelas'         => $infoKelas,
            'kelasTingkat'      => $kelasTingkat,
            'anggota'           => $anggotaRows,
            'jumlahAktif'       => $jumlahAktif,
            'totalAnggota'      => $totalAnggota,
            'totalKelasTingkat' => $kelasTingkat->count(),
            'totalSiswaTingkat' => $kelasTingkat->sum('jumlah'),
            'q'                 => $q,
            'page'              => $page,
            'lastPage'          => $lastPage,
            'opsiSemester'      => $opsiSemester,
            'opsiTingkat'       => $opsiTingkat,
            'opsiPindah'        => $opsiPindah,
        ]);
    }

    public function tambahKelas(Request $request)
    {
        // Kode kelas selalu huruf besar dan tanpa spasi di awal/akhir
        $request->merge([
            'kode' => strtoupper(trim((string) $request->input('kode'))),
        ]);

        $data = $request->validate([
            'kode'       => ['required', 'string', 'max:10', 'regex:/^(X|XI|XII)-[A-Z0-9]{1,3}$/'],
            'tingkat'    => ['required', 'in:X,XI,XII'],
            'wali_kelas' => ['nullable', 'string', 'max:100'],
        ], [
            'kode.required' => 'Kode kelas wajib diisi.',
            'kode.regex'    => 'Format kode harus seperti X-A, XI-IPA-1, atau XII-K. Pakai huruf dan angka, maksimal 3 karakter setelah tingkat.',
            'tingkat.required' => 'Pilih tingkat kelas.',
            'tingkat.in'       => 'Tingkat harus X, XI, atau XII.',
        ]);

        // Kode harus diawali tingkat yang dipilih, misalnya X-K untuk tingkat X
        $awalan = explode('-', $data['kode'])[0];
        if ($awalan !== $data['tingkat']) {
            return back()
                ->withInput()
                ->withErrors(['kode' => 'Kode kelas harus diawali tingkat ' . $data['tingkat'] . ', misalnya ' . $data['tingkat'] . '-A.']);
        }

        $sudahAda = $this->semuaKelas()->contains('kode', $data['kode']);
        if ($sudahAda) {
            return back()
                ->withInput()
                ->withErrors(['kode' => 'Kode kelas ' . $data['kode'] . ' sudah dipakai.']);
        }

        // TODO: simpan $data ke tabel kelas untuk semester aktif.
        return redirect()
            ->route('data-kelas.index', [
                'tingkat' => $data['tingkat'],
                'kelas'   => $data['kode'],
            ])
            ->with('status', 'Kelas ' . $data['kode'] . ' lolos validasi. Penyimpanan ke database belum dihubungkan.');
    }

    public function tambahSiswa(Request $request, string $kelas)
    {
        $request->validate([
            'nis' => ['required', 'string', 'max:20'],
        ], [
            'nis.required' => 'NIS wajib diisi.',
        ]);

        $nis = $request->input('nis');

        // TODO: cari siswa berdasarkan NIS, lalu pasang ke kelas $kelas pada semester aktif.
        return redirect()
            ->route('data-kelas.index', [
                'tingkat' => explode('-', $kelas)[0] ?? 'X',
                'kelas'   => $kelas,
            ])
            ->with('status', 'NIS ' . $nis . ' lolos validasi. Penyimpanan ke database belum dihubungkan.');
    }

    public function pindah(Request $request)
    {
        $data = $request->validate([
            'siswa_id'     => ['required', 'string', 'max:20'],
            'kelas_asal'   => ['required', 'string', 'max:10'],
            'kelas_tujuan' => ['required', 'string', 'max:10', 'different:kelas_asal'],
            'alasan'       => ['nullable', 'string', 'max:255'],
        ], [
            'kelas_tujuan.required'  => 'Pilih kelas tujuan terlebih dahulu.',
            'kelas_tujuan.different' => 'Kelas tujuan harus berbeda dengan kelas asal.',
        ]);

        // TODO: perbarui kelas asal siswa pada semester aktif, lalu simpan alasan pindah.
        return redirect()
            ->route('data-kelas.index', [
                'tingkat' => explode('-', $data['kelas_asal'])[0] ?? 'X',
                'kelas'   => $data['kelas_asal'],
            ])
            ->with('status', 'Permintaan pindah kelas lolos validasi. Penyimpanan ke database belum dihubungkan.');
    }

    /**
     * Daftar kelas pada semester aktif.
     * Format satu kelas: ['kode' => 'X-B', 'tingkat' => 'X', 'wali_kelas' => '...', 'jumlah' => 32]
     * Nantinya diambil dari database.
     */
    private function semuaKelas(): Collection
    {
        return collect();
    }

    /**
     * Anggota per kelas, kuncinya kode kelas.
     * Format satu siswa: ['id' => 1, 'nis' => '2526001', 'nama' => 'Adinda Putri', 'lp' => 'P', 'agama' => 'Islam']
     */
    private function anggotaPerKelas(): Collection
    {
        return collect();
    }
}