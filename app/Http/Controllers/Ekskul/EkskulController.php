<?php

namespace App\Http\Controllers\Ekskul;

use App\Exceptions\AturanBisnisException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ekskul\EkskulRequest;
use App\Http\Requests\Ekskul\PesertaEkskulRequest;
use App\Http\Requests\Ekskul\PredikatEkskulRequest;
use App\Models\Ekskul;
use App\Models\PesertaEkskul;
use App\Models\RiwayatKelas;
use App\Services\AuditService;
use App\Support\AcademicAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Ekskul, peserta per semester (lewat anggota kelas), dan predikat SB/B/K (Kesiswaan).
 * Predikat hanya dicetak pada rapor semester (SAS).
 */
class EkskulController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function dashboard(): JsonResponse
    {
        $semester = AcademicAccess::semesterAktif();
        $peserta = PesertaEkskul::whereHas('riwayatKelas', fn ($q) => $q->where('id_semester', $semester->id_semester));

        return $this->ok([
            'semester' => $semester->only(['id_semester', 'nama']),
            'jumlah_ekskul' => Ekskul::where('aktif', true)->count(),
            'jumlah_peserta' => (clone $peserta)->count(),
            'predikat_terisi' => (clone $peserta)->whereNotNull('predikat')->count(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $semester = AcademicAccess::semester($request->integer('id_semester') ?: null);

        return $this->ok(Ekskul::with('pembina:id_guru,nama')
            ->withCount(['peserta' => fn ($q) => $q->whereHas('riwayatKelas', fn ($w) => $w->where('id_semester', $semester->id_semester))])
            ->orderBy('nama')->get());
    }

    public function store(EkskulRequest $request): JsonResponse
    {
        $e = DB::transaction(function () use ($request) {
            $e = Ekskul::create($request->validated());
            $this->audit->catat('buat_ekskul', 'ekskul', $e->id_ekskul, null, $request->validated());

            return $e;
        });

        return $this->created($e, 'Ekskul ditambahkan.');
    }

    public function update(EkskulRequest $request, Ekskul $ekskul): JsonResponse
    {
        DB::transaction(function () use ($request, $ekskul) {
            $sebelum = $ekskul->only(array_keys($request->validated()));
            $ekskul->update($request->validated());
            $this->audit->catat('ubah_ekskul', 'ekskul', $ekskul->id_ekskul, $sebelum, $request->validated());
        });

        return $this->ok($ekskul->fresh(), 'Ekskul diperbarui.');
    }

    public function destroy(Ekskul $ekskul): JsonResponse
    {
        if ($ekskul->peserta()->exists()) {
            throw AturanBisnisException::terkunci('Ekskul yang sudah memiliki peserta tidak dapat dihapus; nonaktifkan saja.');
        }
        DB::transaction(function () use ($ekskul) {
            $ekskul->delete();
            $this->audit->catat('hapus_ekskul', 'ekskul', $ekskul->id_ekskul, $ekskul->only(['nama']));
        });

        return $this->ok(null, 'Ekskul dihapus.');
    }

    public function peserta(Request $request, Ekskul $ekskul): JsonResponse
    {
        $semester = AcademicAccess::semester($request->integer('id_semester') ?: null);

        $data = PesertaEkskul::with(['riwayatKelas.siswa:id_siswa,nis,nama', 'riwayatKelas.kelas:id_kelas,nama'])
            ->where('id_ekskul', $ekskul->id_ekskul)
            ->whereHas('riwayatKelas', fn ($q) => $q->where('id_semester', $semester->id_semester))->get()
            ->sortBy(fn ($p) => [$p->riwayatKelas->kelas->nama, mb_strtolower($p->riwayatKelas->siswa->nama)])->values();

        return $this->ok($data->map(fn (PesertaEkskul $p) => [
            'id_peserta_ekskul' => $p->id_peserta_ekskul,
            'siswa' => $p->riwayatKelas->siswa,
            'kelas' => $p->riwayatKelas->kelas,
            'predikat' => $p->predikat,
            'catatan' => $p->catatan,
        ]));
    }

    public function tambahPeserta(PesertaEkskulRequest $request, Ekskul $ekskul): JsonResponse
    {
        if (! $ekskul->aktif) {
            throw new AturanBisnisException('Ekskul tidak aktif.');
        }
        $n = DB::transaction(function () use ($request, $ekskul) {
            $n = 0;
            foreach (RiwayatKelas::whereKey($request->input('id_riwayat_kelas'))->get() as $a) {
                $p = PesertaEkskul::firstOrCreate(['id_ekskul' => $ekskul->id_ekskul, 'id_riwayat_kelas' => $a->id_riwayat_kelas]);
                $n += (int) $p->wasRecentlyCreated;
            }
            $this->audit->catat('tambah_peserta_ekskul', 'ekskul', $ekskul->id_ekskul, null, ['ditambahkan' => $n]);

            return $n;
        });

        return $this->ok(['ditambahkan' => $n], "{$n} peserta ditambahkan.");
    }

    public function hapusPeserta(PesertaEkskul $peserta): JsonResponse
    {
        DB::transaction(function () use ($peserta) {
            $peserta->delete();
            $this->audit->catat('hapus_peserta_ekskul', 'peserta_ekskul', $peserta->id_peserta_ekskul, $peserta->only(['id_ekskul', 'id_riwayat_kelas', 'predikat']));
        });

        return $this->ok(null, 'Peserta dihapus.');
    }

    public function predikat(PredikatEkskulRequest $request, Ekskul $ekskul): JsonResponse
    {
        $items = collect($request->validated('items'))->keyBy('id_peserta_ekskul');
        $peserta = PesertaEkskul::where('id_ekskul', $ekskul->id_ekskul)->whereKey($items->keys())->get();
        if ($peserta->count() !== $items->count()) {
            throw new AturanBisnisException('Ada peserta yang bukan anggota ekskul ini.', 'VALIDATION_ERROR', 422, ['items' => ['Peserta tidak ditemukan pada ekskul ini.']]);
        }

        DB::transaction(function () use ($peserta, $items, $ekskul) {
            foreach ($peserta as $p) {
                $i = $items[$p->id_peserta_ekskul];
                $p->update(['predikat' => $i['predikat'], 'catatan' => $i['catatan'] ?? $p->catatan, 'diisi_oleh' => Auth::id()]);
            }
            $this->audit->catat('isi_predikat_ekskul', 'ekskul', $ekskul->id_ekskul, null, ['jumlah' => $peserta->count()]);
        });

        return $this->ok(['tersimpan' => $peserta->count()], 'Predikat tersimpan.');
    }
}
