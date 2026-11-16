<?php

namespace App\Http\Controllers\Master;

use App\Exceptions\AturanBisnisException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\SiswaRequest;
use App\Models\Semester;
use App\Models\Siswa;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiswaController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:'.implode(',', Siswa::STATUS)],
            'id_kelas' => ['nullable', 'integer'],
            'id_semester' => ['nullable', 'integer', 'exists:semester,id_semester'],
            'id_agama' => ['nullable', 'integer'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'tanpa_kelas' => ['nullable', 'boolean'],
            'tanpa_akun' => ['nullable', 'boolean'],
        ]);
        $idSemester = $request->integer('id_semester') ?: Semester::aktif()?->id_semester;

        $page = Siswa::query()
            ->with(['agama:id_agama,nama', 'riwayatKelas' => fn ($q) => $q->where('id_semester', $idSemester)->with('kelas:id_kelas,nama,tingkat')])
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('nama', 'like', "%{$s}%")->orWhere('nis', 'like', "%{$s}%")->orWhere('nisn', 'like', "%{$s}%")))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->id_agama, fn ($q, $v) => $q->where('id_agama', $v))
            ->when($request->jenis_kelamin, fn ($q, $v) => $q->where('jenis_kelamin', $v))
            ->when($request->id_kelas, fn ($q, $v) => $q->whereHas('riwayatKelas', fn ($w) => $w->where('id_kelas', $v)->where('id_semester', $idSemester)))
            ->when($request->boolean('tanpa_kelas'), fn ($q) => $q->whereDoesntHave('riwayatKelas', fn ($w) => $w->where('id_semester', $idSemester)))
            ->when($request->boolean('tanpa_akun'), fn ($q) => $q->whereNull('id_user'))
            ->orderBy('nama')
            ->paginate($this->perPage());

        return $this->paginated($page, fn (Siswa $s) => $this->bentuk($s));
    }

    public function show(Siswa $siswa): JsonResponse
    {
        $siswa->load(['agama', 'orangTua', 'riwayatKelas.kelas', 'riwayatKelas.semester.tahunAjaran']);

        return $this->ok($this->bentuk($siswa) + [
            'orang_tua' => $siswa->orangTua->map(fn ($o) => ['id_orang_tua' => $o->id_orang_tua, 'nama' => $o->nama, 'hubungan' => $o->pivot->hubungan, 'punya_akun' => $o->id_user !== null]),
            'riwayat_kelas' => $siswa->riwayatKelas->map(fn ($r) => [
                'id_riwayat_kelas' => $r->id_riwayat_kelas, 'kelas' => $r->kelas->nama,
                'semester' => $r->semester->nama, 'tahun_ajaran' => $r->semester->tahunAjaran->nama,
            ]),
        ]);
    }

    public function store(SiswaRequest $request): JsonResponse
    {
        $siswa = DB::transaction(function () use ($request) {
            $s = Siswa::create($request->validated());
            $this->audit->catat('buat_siswa', 'siswa', $s->id_siswa, null, $request->validated());

            return $s;
        });

        return $this->created($this->bentuk($siswa->fresh('agama')), 'Siswa ditambahkan.');
    }

    public function update(SiswaRequest $request, Siswa $siswa): JsonResponse
    {
        DB::transaction(function () use ($request, $siswa) {
            $sebelum = $siswa->only(array_keys($request->validated()));
            $siswa->update($request->validated());
            $this->audit->catat('ubah_siswa', 'siswa', $siswa->id_siswa, $sebelum, $request->validated());
        });

        return $this->ok($this->bentuk($siswa->fresh('agama')), 'Data siswa diperbarui.');
    }

    /** Hapus hanya untuk data salah input yang belum pernah ditempatkan di kelas. */
    public function destroy(Siswa $siswa): JsonResponse
    {
        if ($siswa->riwayatKelas()->exists() || $siswa->id_user) {
            throw AturanBisnisException::terkunci('Siswa sudah memiliki riwayat kelas atau akun. Ubah status menjadi pindah/keluar, bukan dihapus.');
        }

        DB::transaction(function () use ($siswa) {
            $siswa->orangTua()->detach();
            $siswa->delete();
            $this->audit->catat('hapus_siswa', 'siswa', $siswa->id_siswa, $siswa->only(['nis', 'nisn', 'nama']));
        });

        return $this->ok(null, 'Siswa dihapus.');
    }

    private function bentuk(Siswa $s): array
    {
        $kelas = $s->relationLoaded('riwayatKelas') ? $s->riwayatKelas->sortByDesc('id_semester')->first() : null;

        return $s->only(['id_siswa', 'nis', 'nisn', 'nama', 'jenis_kelamin', 'id_agama', 'tempat_lahir', 'alamat', 'tahun_masuk', 'status']) + [
            'tanggal_lahir' => $s->tanggal_lahir?->format('Y-m-d'),
            'agama' => $s->agama?->nama,
            'punya_akun' => $s->id_user !== null,
            'kelas' => $kelas ? ['id_riwayat_kelas' => $kelas->id_riwayat_kelas, 'id_kelas' => $kelas->id_kelas, 'nama' => $kelas->kelas?->nama] : null,
        ];
    }
}
