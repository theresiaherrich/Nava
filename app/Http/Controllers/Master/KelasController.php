<?php

namespace App\Http\Controllers\Master;

use App\Exceptions\AturanBisnisException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\AnggotaKelasRequest;
use App\Http\Requests\Master\KelasRequest;
use App\Http\Requests\Master\PindahKelasRequest;
use App\Models\Kelas;
use App\Models\RiwayatKelas;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Services\AnggotaKelasService;
use App\Services\AuditService;
use App\Support\AcademicAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    public function __construct(private AuditService $audit, private AnggotaKelasService $anggota) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'id_tahun_ajaran' => ['nullable', 'integer', 'exists:tahun_ajaran,id_tahun_ajaran'],
            'tingkat' => ['nullable', 'in:'.implode(',', Kelas::TINGKAT)],
            'q' => ['nullable', 'string', 'max:50'],
        ]);
        $ta = $request->integer('id_tahun_ajaran') ?: TahunAjaran::where('aktif', true)->value('id_tahun_ajaran');
        $semester = Semester::aktif();

        $kelas = Kelas::where('id_tahun_ajaran', $ta)
            ->when($request->tingkat, fn ($q, $v) => $q->where('tingkat', $v))
            ->when($request->q, fn ($q, $v) => $q->where('nama', 'like', "%{$v}%"))
            ->withCount(['anggota as jumlah_siswa' => fn ($q) => $q->where('id_semester', $semester?->id_semester)])
            ->with(['wali' => fn ($q) => $q->where('id_semester', $semester?->id_semester)->with('guru:id_guru,nama')])
            ->orderByRaw("CASE tingkat WHEN 'X' THEN 1 WHEN 'XI' THEN 2 ELSE 3 END")->orderBy('nama')
            ->paginate($this->perPage());

        return $this->paginated($kelas, fn (Kelas $k) => $k->only(['id_kelas', 'id_tahun_ajaran', 'nama', 'tingkat', 'fase', 'jumlah_siswa']) + [
            'wali_kelas' => $k->wali->first()?->guru?->only(['id_guru', 'nama']),
        ]);
    }

    public function store(KelasRequest $request): JsonResponse
    {
        $data = $request->validated() + ['fase' => Kelas::faseUntuk($request->string('tingkat'))];
        $kelas = DB::transaction(function () use ($data) {
            $k = Kelas::create($data);
            $this->audit->catat('buat_kelas', 'kelas', $k->id_kelas, null, $data);

            return $k;
        });

        return $this->created($kelas, 'Kelas ditambahkan.');
    }

    public function update(KelasRequest $request, Kelas $kelas): JsonResponse
    {
        $data = $request->validated();
        if (isset($data['tingkat'])) {
            if ($data['tingkat'] !== $kelas->tingkat && $kelas->anggota()->exists()) {
                throw AturanBisnisException::terkunci('Tingkat kelas yang sudah memiliki anggota tidak dapat diubah.');
            }
            $data['fase'] = Kelas::faseUntuk($data['tingkat']);
        }

        DB::transaction(function () use ($kelas, $data) {
            $sebelum = $kelas->only(array_keys($data));
            $kelas->update($data);
            $this->audit->catat('ubah_kelas', 'kelas', $kelas->id_kelas, $sebelum, $data);
        });

        return $this->ok($kelas->fresh(), 'Kelas diperbarui.');
    }

    public function destroy(Kelas $kelas): JsonResponse
    {
        if ($kelas->anggota()->exists() || $kelas->wali()->exists()) {
            throw AturanBisnisException::terkunci('Kelas yang sudah memiliki anggota atau wali tidak dapat dihapus.');
        }
        DB::transaction(function () use ($kelas) {
            $kelas->delete();
            $this->audit->catat('hapus_kelas', 'kelas', $kelas->id_kelas, $kelas->only(['nama', 'tingkat', 'id_tahun_ajaran']));
        });

        return $this->ok(null, 'Kelas dihapus.');
    }

    public function anggota(Request $request, Kelas $kelas): JsonResponse
    {
        $semester = AcademicAccess::semester($request->integer('id_semester') ?: null);
        $this->anggota->pastikanSemesterKelas($kelas, $semester);

        $data = RiwayatKelas::with('siswa:id_siswa,nis,nisn,nama,jenis_kelamin,status,id_user')
            ->where('id_kelas', $kelas->id_kelas)->where('id_semester', $semester->id_semester)
            ->get()->sortBy('siswa.nama', SORT_NATURAL | SORT_FLAG_CASE)->values()
            ->map(fn (RiwayatKelas $r) => ['id_riwayat_kelas' => $r->id_riwayat_kelas] + $r->siswa->only(['id_siswa', 'nis', 'nisn', 'nama', 'jenis_kelamin', 'status']));

        return $this->ok(['kelas' => $kelas->only(['id_kelas', 'nama', 'tingkat']), 'id_semester' => $semester->id_semester, 'anggota' => $data]);
    }

    public function tambahAnggota(AnggotaKelasRequest $request, Kelas $kelas): JsonResponse
    {
        $semester = AcademicAccess::semester($request->integer('id_semester') ?: null);
        $hasil = $this->anggota->tambah($kelas, $semester, $request->input('id_siswa'));

        return $this->ok($hasil, "{$hasil['ditambahkan']} siswa ditambahkan ke {$kelas->nama}.");
    }

    public function pindah(PindahKelasRequest $request, RiwayatKelas $anggota): JsonResponse
    {
        $hasil = $this->anggota->pindah($anggota->load('kelas'), Kelas::findOrFail($request->integer('id_kelas')), $request->string('alasan'));

        return $this->ok(['id_riwayat_kelas' => $hasil->id_riwayat_kelas, 'kelas' => $hasil->kelas->only(['id_kelas', 'nama'])], 'Siswa dipindahkan.');
    }

    public function keluarkan(RiwayatKelas $anggota): JsonResponse
    {
        $this->anggota->keluarkan($anggota);

        return $this->ok(null, 'Siswa dikeluarkan dari kelas.');
    }
}
