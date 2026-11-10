<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RelasiOrangTuaController extends Controller
{
    private const PER_PAGE = 8;

    public function index(Request $request)
    {
        $status   = (string) $request->query('status', 'semua');
        $hubungan = (string) $request->query('hubungan', 'semua');
        $q        = trim((string) $request->query('q', ''));
        $page     = max(1, (int) $request->query('page', 1));

        $semua = $this->semuaRelasi();

        $hasil = $semua
            ->when($status !== 'semua', fn ($c) => $c->where('status', $status))
            ->when($hubungan !== 'semua', fn ($c) => $c->where('hubungan', $hubungan))
            ->when($q !== '', fn ($c) => $c->filter(fn ($r) => $this->cocokCari($r, $q)))
            ->values();

        $totalFiltered = $hasil->count();
        $lastPage      = max(1, (int) ceil($totalFiltered / self::PER_PAGE));
        $page          = min($page, $lastPage);
        $rows          = $hasil->forPage($page, self::PER_PAGE)->values();

        // Siswa yang sudah punya relasi, lalu dihitung yang belum
        $idTerhubung = $semua
            ->flatMap(fn ($r) => collect($r['anak'] ?? [])->pluck('id'))
            ->map(fn ($id) => (string) $id)
            ->all();

        $belumTerhubung = $this->siswaTersedia()
            ->reject(fn ($s) => in_array((string) $s['id'], $idTerhubung, true))
            ->count();

        return view('relasi-orang-tua.index', [
            'rows'           => $rows,
            'page'           => $page,
            'lastPage'       => $lastPage,
            'totalFiltered'  => $totalFiltered,
            'status'         => $status,
            'hubungan'       => $hubungan,
            'q'              => $q,
            'stats'          => [
                'ortu_terhubung'  => $semua->count(),
                'belum_terhubung' => $belumTerhubung,
            ],
            'opsiStatus'   => [
                'semua'          => 'Status: Semua',
                'terverifikasi'  => 'Status: Terverifikasi',
                'menunggu'       => 'Status: Menunggu',
            ],
            'opsiHubungan' => [
                'semua' => 'Hubungan: Semua',
                'ayah'  => 'Hubungan: Ayah',
                'ibu'   => 'Hubungan: Ibu',
                'wali'  => 'Hubungan: Wali',
            ],
            'akunOrtu'  => $this->akunOrtuTersedia()->values(),
            'siswaList' => $this->siswaTersedia()->values(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        $akunValid = $this->akunOrtuTersedia()
            ->contains(fn ($a) => (string) $a['id'] === (string) $data['akun_ortu_id']);

        if (! $akunValid) {
            return back()->withInput()->withErrors([
                'akun_ortu_id' => 'Akun orang tua tidak ditemukan.',
            ]);
        }

        $idSiswa = $this->siswaTersedia()->map(fn ($s) => (string) $s['id'])->all();
        $tidakDitemukan = collect($data['siswa_ids'])->diff($idSiswa);

        if ($tidakDitemukan->isNotEmpty()) {
            return back()->withInput()->withErrors([
                'siswa_ids' => 'Ada siswa yang tidak ditemukan. Muat ulang halaman lalu coba lagi.',
            ]);
        }

        // TODO: simpan relasi akun orang tua ke setiap siswa dengan hubungan $data['hubungan'].
        return back()->with('status', 'Relasi orang tua lolos validasi. Penyimpanan ke database belum dihubungkan.');
    }

    public function update(Request $request, string $ortu)
    {
        if (! $this->semuaRelasi()->contains(fn ($r) => (string) $r['id'] === $ortu)) {
            abort(404);
        }

        $this->validasi($request);

        // TODO: sinkronkan daftar anak dan hubungan untuk orang tua $ortu.
        return back()->with('status', 'Perubahan relasi lolos validasi. Penyimpanan ke database belum dihubungkan.');
    }

    public function destroy(string $ortu)
    {
        if (! $this->semuaRelasi()->contains(fn ($r) => (string) $r['id'] === $ortu)) {
            abort(404);
        }

        // TODO: hapus semua relasi anak untuk orang tua $ortu.
        return back()->with('status', 'Permintaan hapus relasi diterima. Penghapusan ke database belum dihubungkan.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'akun_ortu_id' => ['required', 'string', 'max:20'],
            'siswa_ids'    => ['required', 'array', 'min:1'],
            'siswa_ids.*'  => ['required', 'string', 'max:20'],
            'hubungan'     => ['required', 'in:ayah,ibu,wali'],
        ], [
            'akun_ortu_id.required' => 'Pilih akun orang tua terlebih dahulu.',
            'siswa_ids.required'    => 'Pilih minimal satu anak.',
            'siswa_ids.min'         => 'Pilih minimal satu anak.',
            'hubungan.required'     => 'Pilih hubungan orang tua.',
            'hubungan.in'           => 'Hubungan harus Ayah, Ibu, atau Wali.',
        ]);
    }

    private function cocokCari(array $r, string $q): bool
    {
        $q = mb_strtolower($q);

        if (str_contains(mb_strtolower($r['nama'] ?? ''), $q)) {
            return true;
        }

        if (str_contains(mb_strtolower($r['username'] ?? ''), $q)) {
            return true;
        }

        return collect($r['anak'] ?? [])
            ->contains(fn ($a) => str_contains(mb_strtolower($a['nama'] ?? ''), $q));
    }

    /**
     * Relasi per orang tua. Format satu baris:
     * ['id' => 1, 'nama' => 'Bapak Hendra Gunawan', 'username' => 'hendra.g', 'hubungan' => 'ayah',
     *  'status' => 'terverifikasi', // terverifikasi | menunggu
     *  'anak' => [['id' => 1, 'nama' => 'Adinda Putri', 'kelas' => 'X-B']]]
     * Nantinya diambil dari database.
     */
    private function semuaRelasi(): Collection
    {
        return collect();
    }

    /**
     * Akun orang tua untuk pilihan di modal. Format: ['id' => 1, 'nama' => '...', 'username' => '...']
     */
    private function akunOrtuTersedia(): Collection
    {
        return collect();
    }

    /**
     * Siswa untuk pilihan anak. Format: ['id' => 1, 'nama' => '...', 'nis' => '2526001', 'kelas' => 'X-B']
     */
    private function siswaTersedia(): Collection
    {
        return collect();
    }
}