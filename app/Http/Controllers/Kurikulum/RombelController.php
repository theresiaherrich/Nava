<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kurikulum\RombelGuruRequest;
use App\Http\Requests\Kurikulum\RombelPesertaRequest;
use App\Http\Requests\Kurikulum\RombelRequest;
use App\Models\Rombel;
use App\Models\SiswaRombel;
use App\Services\RombelService;
use App\Support\AcademicAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Rombel & guru pengampu (Kurikulum). */
class RombelController extends Controller
{
    public function __construct(private RombelService $rombel) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'id_semester' => ['nullable', 'integer', 'exists:semester,id_semester'],
            'id_mapel' => ['nullable', 'integer'],
            'id_guru' => ['nullable', 'integer'],
            'id_kelas' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);
        $semester = AcademicAccess::semester($request->integer('id_semester') ?: null);

        $page = Rombel::with(['mapel:id_mapel,kode,nama,id_agama', 'guru:id_guru,nama'])
            ->withCount(['peserta', 'tp'])
            ->where('id_semester', $semester->id_semester)
            ->when($request->id_mapel, fn ($q, $v) => $q->where('id_mapel', $v))
            ->when($request->id_guru, fn ($q, $v) => $q->whereHas('guru', fn ($w) => $w->where('guru.id_guru', $v)))
            ->when($request->id_kelas, fn ($q, $v) => $q->whereHas('peserta.riwayatKelas', fn ($w) => $w->where('id_kelas', $v)))
            ->when($request->q, fn ($q, $v) => $q->where('nama', 'like', "%{$v}%"))
            ->orderBy('nama')
            ->paginate($this->perPage());

        return $this->paginated($page);
    }

    public function show(Rombel $rombel): JsonResponse
    {
        $rombel->load(['mapel', 'guru:id_guru,nama,nip', 'semester.tahunAjaran', 'tp',
            'peserta.riwayatKelas.siswa:id_siswa,nis,nama,id_agama', 'peserta.riwayatKelas.kelas:id_kelas,nama,tingkat']);

        return $this->ok([
            'rombel' => $rombel->only(['id_rombel', 'nama', 'id_semester', 'id_mapel']),
            'mapel' => $rombel->mapel,
            'semester' => ['id_semester' => $rombel->semester->id_semester, 'nama' => $rombel->semester->nama, 'tahun_ajaran' => $rombel->semester->tahunAjaran->nama],
            'guru' => $rombel->guru,
            'tp' => $rombel->tp,
            'kelas_asal' => $rombel->peserta->pluck('riwayatKelas.kelas')->unique('id_kelas')->sortBy('nama')->values(),
            'peserta' => $rombel->peserta->map(fn (SiswaRombel $p) => [
                'id_siswa_rombel' => $p->id_siswa_rombel,
                'id_riwayat_kelas' => $p->id_riwayat_kelas,
                'siswa' => $p->riwayatKelas->siswa->only(['id_siswa', 'nis', 'nama']),
                'kelas' => $p->riwayatKelas->kelas->only(['id_kelas', 'nama']),
            ])->sortBy('siswa.nama')->values(),
        ]);
    }

    public function store(RombelRequest $request): JsonResponse
    {
        $data = $request->safe()->only(['id_semester', 'id_mapel', 'nama']);

        return $this->created($this->rombel->buat($data, $request->input('id_guru', [])), 'Rombel dibuat.');
    }

    public function update(RombelRequest $request, Rombel $rombel): JsonResponse
    {
        return $this->ok($this->rombel->ubah($rombel, $request->safe()->only(['nama'])), 'Rombel diperbarui.');
    }

    public function destroy(Rombel $rombel): JsonResponse
    {
        $this->rombel->hapus($rombel);

        return $this->ok(null, 'Rombel dihapus.');
    }

    public function setGuru(RombelGuruRequest $request, Rombel $rombel): JsonResponse
    {
        return $this->ok($this->rombel->setGuru($rombel, $request->input('id_guru')), 'Guru pengampu diperbarui.');
    }

    public function tambahPeserta(RombelPesertaRequest $request, Rombel $rombel): JsonResponse
    {
        $hasil = $this->rombel->tambahPeserta($rombel, $request->input('id_riwayat_kelas', []), $request->input('id_kelas', []));

        return $this->ok($hasil, "{$hasil['ditambahkan']} peserta ditambahkan.");
    }

    public function hapusPeserta(Rombel $rombel, SiswaRombel $peserta): JsonResponse
    {
        $this->rombel->hapusPeserta($rombel, $peserta);

        return $this->ok(null, 'Peserta dikeluarkan dari rombel.');
    }
}
