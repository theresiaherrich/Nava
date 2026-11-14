<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\LogAktivitas;
use App\Models\NilaiTp;
use App\Models\PaketNilai;
use App\Models\PeriodeNilai;
use App\Models\Rombel;
use App\Models\SiswaRombel;
use App\Models\User;
use App\Models\VerifikasiNilai;
use App\Models\WaliKelas;
use App\Services\AuditService;
use App\Services\PenilaianAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Alur: draft → dikirim (guru) → disetujui (wali kelas) → dikunci (kurikulum).
 * Minta revisi: dikirim → perlu_perbaikan. Buka koreksi: dikunci → perlu_perbaikan (wajib alasan).
 * Usulan state machine; belum final sampai disepakati sekolah.
 */
class PaketNilaiController extends Controller
{
    /** Pemetaan status BE (paket_nilai.status) ke status FE (panduan bagian 5). */
    private const STATUS_FE = [
        'draft' => 'sedang-diisi',
        'perlu_perbaikan' => 'perlu-revisi',
        'dikirim' => 'terkirim',
        'disetujui' => 'disetujui',
        'dikunci' => 'final',
    ];

    /** BE memakai angka tingkat (10, 11, 12); FE memakai X, XI, XII. */
    private const TINGKAT_ROMAWI = [10 => 'X', 11 => 'XI', 12 => 'XII'];

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'periode_id' => ['required', 'integer', 'exists:periode_nilai,id'],
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
        ]);
        $user = $request->user();

        $query = PaketNilai::with(['rombel.mapel', 'kelas', 'periode'])
            ->where('periode_id', $data['periode_id'])
            ->when($data['kelas_id'] ?? null, fn ($q, $id) => $q->where('kelas_id', $id))
            ->orderBy('id');

        // Wali kelas hanya melihat paket kelasnya; guru hanya paket rombel yang ia ampu.
        $this->batasiAkses($query, $user);

        return response()->json($query->paginate(20)->through(fn (PaketNilai $p) => $this->tambahStatusFe($p)));
    }

    /** GET /paket-nilai/{record}  (admin, kurikulum, guru pengampu, wali kelas kelas tersebut) */
    public function show(Request $request, PaketNilai $record): JsonResponse
    {
        abort_unless($this->bisaMelihat($request->user(), $record), 403, 'Anda tidak berhak melihat paket nilai ini.');

        $record->load([
            'rombel.mapel', 'kelas', 'periode',
            'verifikasi' => fn ($q) => $q->orderBy('id'),
            'verifikasi.user:id,name',
        ]);

        // TP yang wajib diisi pada periode ini dan peserta dari kelas asal paket.
        $tpWajib = $record->rombel->tp()->get()
            ->filter(fn ($t) => PenilaianAccess::tpDapatDiisi($record->periode, $t))
            ->values();
        $peserta = SiswaRombel::with('siswaKelas.siswa')->where('rombel_id', $record->rombel_id)->get()
            ->filter(fn ($p) => (int) $p->siswaKelas->kelas_id === (int) $record->kelas_id)
            ->values();
        $nilai = NilaiTp::whereIn('siswa_rombel_id', $peserta->pluck('id'))
            ->whereIn('tp_id', $tpWajib->pluck('id'))
            ->get()
            ->groupBy('siswa_rombel_id');

        $terisi = 0;
        $baris = $peserta->map(function (SiswaRombel $p) use ($nilai, $tpWajib, &$terisi) {
            $skorPerTp = $nilai->get($p->id, collect())->keyBy('tp_id');
            $perTp = $tpWajib->map(function ($tp) use ($skorPerTp) {
                $n = $skorPerTp->get($tp->id);

                // null = belum diisi, bukan 0.
                return ['tp_id' => $tp->id, 'skor' => $n === null || $n->skor === null ? null : (float) $n->skor];
            });
            $jumlahTerisi = $perTp->filter(fn ($i) => $i['skor'] !== null)->count();
            $terisi += $jumlahTerisi;

            return [
                'siswa_rombel_id' => $p->id,
                'siswa_id' => $p->siswaKelas->siswa_id,
                'nis' => $p->siswaKelas->siswa->nis,
                'nama' => $p->siswaKelas->siswa->nama,
                'nilai' => $perTp->all(),
                'lengkap' => $jumlahTerisi === $tpWajib->count(),
            ];
        })->all();

        // Siswa yang ditandai wali pada permintaan revisi terakhir (hanya relevan saat paket dikembalikan).
        $siswaRevisi = [];
        if ($record->status === 'perlu_perbaikan' && $record->verifikasi->last()?->keputusan === 'perlu_revisi') {
            $log = LogAktivitas::where('nama_entitas', 'paket_nilai')
                ->where('entitas_id', $record->id)
                ->where('aksi', 'minta_revisi')
                ->latest('id')
                ->first();
            $siswaRevisi = $log?->data_sesudah['siswa_ids'] ?? [];
        }

        $this->tambahStatusFe($record);
        $sel = $peserta->count() * $tpWajib->count();

        return response()->json(['data' => $record->toArray() + [
            'tingkat_label' => self::TINGKAT_ROMAWI[(int) $record->kelas->tingkat] ?? (string) $record->kelas->tingkat,
            'tp' => $tpWajib->map(fn ($t) => ['id' => $t->id, 'nomor_urut' => $t->nomor_urut, 'deskripsi' => $t->deskripsi])->all(),
            'peserta' => $baris,
            'siswa_revisi' => $siswaRevisi,
            'ringkasan' => [
                'jumlah_peserta' => $peserta->count(),
                'jumlah_tp' => $tpWajib->count(),
                'sel_terisi' => $terisi,
                'sel_kosong' => $sel - $terisi,
            ],
        ]]);
    }

    /** POST /paket-nilai/kirim  body: rombel_id, periode_id, kelas_id */
    public function kirim(Request $request): JsonResponse
    {
        $data = $request->validate([
            'rombel_id' => ['required', 'integer', 'exists:rombel,id'],
            'periode_id' => ['required', 'integer', 'exists:periode_nilai,id'],
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
        ]);
        $user = $request->user();
        $rombel = Rombel::findOrFail($data['rombel_id']);
        $kelas = Kelas::findOrFail($data['kelas_id']);
        abort_unless(PenilaianAccess::bisaInputNilai($user, $rombel), 403, 'Hanya guru pengampu yang dapat mengirim nilai rombel ini.');

        $paket = DB::transaction(function () use ($user, $data, $rombel, $kelas) {
            $periode = PeriodeNilai::whereKey($data['periode_id'])->lockForUpdate()->firstOrFail();
            abort_unless($periode->terbuka(), 422, 'Periode penilaian tertutup. Nilai tidak dapat dikirim.');
            abort_if((int) $kelas->tingkat !== (int) $periode->tingkat, 422, 'Tingkat kelas tidak sesuai dengan periode.');
            abort_if((int) $kelas->semester_id !== (int) $rombel->semester_id, 422, 'Kelas asal dan rombel berasal dari semester berbeda.');

            $paket = PaketNilai::firstOrCreate(
                ['rombel_id' => $rombel->id, 'periode_id' => $periode->id, 'kelas_id' => $kelas->id],
                ['status' => 'draft'],
            );
            $paket = PaketNilai::whereKey($paket->id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($paket->status, ['draft', 'perlu_perbaikan'], true), 422, "Paket berstatus {$paket->status} dan tidak dapat dikirim.");

            $tpWajib = $rombel->tp()->get()->filter(fn ($t) => PenilaianAccess::tpDapatDiisi($periode, $t));
            $peserta = SiswaRombel::with('siswaKelas')->where('rombel_id', $rombel->id)->get()
                ->filter(fn ($p) => (int) $p->siswaKelas->kelas_id === (int) $kelas->id);

            $sel = $peserta->count() * $tpWajib->count();
            abort_if($sel === 0, 422, 'Belum ada TP atau peserta pada kelas ini.');

            $terisi = NilaiTp::whereIn('siswa_rombel_id', $peserta->pluck('id'))
                ->whereIn('tp_id', $tpWajib->pluck('id'))
                ->whereNotNull('skor')
                ->count();
            $kosong = $sel - $terisi;
            abort_if($kosong > 0, 422, "Nilai belum lengkap: {$kosong} sel belum diisi. Nilai tidak dapat dikirim.");

            $sebelum = $paket->status;
            $paket->update(['status' => 'dikirim', 'dikirim_oleh' => $user->id, 'waktu_kirim' => now()]);
            AuditService::record('kirim_nilai', 'paket_nilai', $paket->id, ['status' => $sebelum], ['status' => 'dikirim'], null, $user->id);

            return $paket;
        });

        return response()->json(['data' => $this->tambahStatusFe($paket)]);
    }

    /** POST /paket-nilai/{record}/setujui  (wali kelas) */
    public function setujui(Request $request, PaketNilai $record): JsonResponse
    {
        abort_unless(PenilaianAccess::bisaMemeriksaKelas($request->user(), $record->kelas_id), 403, 'Anda bukan wali kelas untuk kelas asal paket ini.');
        $data = $request->validate(['catatan' => ['nullable', 'string', 'max:1000']]);

        return response()->json(['data' => $this->tambahStatusFe($this->ubahStatus($request, $record, 'dikirim', 'disetujui', 'disetujui', 'setujui_nilai', $data['catatan'] ?? null))]);
    }

    /** POST /paket-nilai/{record}/revisi  (wali kelas; body: catatan wajib, siswa_ids[] opsional — kosong = seluruh paket) */
    public function revisi(Request $request, PaketNilai $record): JsonResponse
    {
        abort_unless(PenilaianAccess::bisaMemeriksaKelas($request->user(), $record->kelas_id), 403, 'Anda bukan wali kelas untuk kelas asal paket ini.');
        $data = $request->validate([
            'catatan' => ['required', 'string', 'min:5', 'max:1000'],
            'siswa_ids' => ['nullable', 'array'],
            'siswa_ids.*' => ['integer', 'distinct'],
        ]);

        $siswaIds = array_values(array_unique(array_map('intval', $data['siswa_ids'] ?? [])));
        if ($siswaIds !== [] && $this->siswaIdsPaket($record)->intersect($siswaIds)->count() !== count($siswaIds)) {
            throw ValidationException::withMessages(['siswa_ids' => 'Sebagian siswa bukan peserta paket nilai ini.']);
        }

        // Siswa terpilih dicatat di log aktivitas (data_sesudah.siswa_ids); belum ada tabel khusus per siswa.
        $paket = $this->ubahStatus($request, $record, 'dikirim', 'perlu_perbaikan', 'perlu_revisi', 'minta_revisi', $data['catatan'], ['siswa_ids' => $siswaIds]);
        $paket->setAttribute('siswa_ids', $siswaIds);

        return response()->json(['data' => $this->tambahStatusFe($paket)]);
    }

    /** POST /paket-nilai/{record}/kunci  (kurikulum) */
    public function kunci(Request $request, PaketNilai $record): JsonResponse
    {
        return response()->json(['data' => $this->tambahStatusFe($this->ubahStatus($request, $record, 'disetujui', 'dikunci', 'dikunci', 'kunci_nilai', null))]);
    }

    /** POST /paket-nilai/{record}/buka-koreksi  (kurikulum, alasan wajib) */
    public function bukaKoreksi(Request $request, PaketNilai $record): JsonResponse
    {
        $data = $request->validate(['alasan' => ['required', 'string', 'min:10', 'max:1000']]);

        return response()->json(['data' => $this->tambahStatusFe($this->ubahStatus($request, $record, 'dikunci', 'perlu_perbaikan', 'buka_koreksi', 'buka_koreksi', $data['alasan']))]);
    }

    private function ubahStatus(Request $request, PaketNilai $record, string $dari, string $ke, string $keputusan, string $aksi, ?string $catatan, ?array $tambahan = null): PaketNilai
    {
        $user = $request->user();

        return DB::transaction(function () use ($record, $dari, $ke, $keputusan, $aksi, $catatan, $tambahan, $user) {
            $paket = PaketNilai::whereKey($record->id)->lockForUpdate()->firstOrFail();
            abort_unless($paket->status === $dari, 422, "Status paket harus '{$dari}', saat ini '{$paket->status}'.");

            $sebelum = $paket->status;
            $perubahan = ['status' => $ke];
            if ($ke === 'dikunci') {
                $perubahan += ['dikunci_oleh' => $user->id, 'waktu_kunci' => now()];
            }
            $paket->update($perubahan);

            VerifikasiNilai::create([
                'paket_id' => $paket->id,
                'user_id' => $user->id,
                'keputusan' => $keputusan,
                'catatan' => $catatan,
            ]);
            AuditService::record($aksi, 'paket_nilai', $paket->id, ['status' => $sebelum], ['status' => $ke] + ($tambahan ?? []), $catatan, $user->id);

            return $paket;
        });
    }

    /** Admin/kurikulum semua; wali kelas hanya kelasnya; guru hanya rombel yang ia ampu. */
    private function bisaMelihat(User $user, PaketNilai $paket): bool
    {
        if ($user->hasAnyRole(['admin', 'kurikulum'])) {
            return true;
        }
        if (PenilaianAccess::bisaMemeriksaKelas($user, (int) $paket->kelas_id)) {
            return true;
        }

        return $paket->rombel !== null && PenilaianAccess::bisaInputNilai($user, $paket->rombel);
    }

    /** Versi query dari bisaMelihat() untuk daftar paket. */
    private function batasiAkses($query, User $user): void
    {
        if ($user->hasAnyRole(['admin', 'kurikulum'])) {
            return;
        }
        $guruId = PenilaianAccess::guruId($user);
        $kelasIds = $guruId !== null && $user->hasAnyRole(['wali_kelas'])
            ? WaliKelas::where('guru_id', $guruId)->pluck('kelas_id')
            : collect();
        $rombelIds = $guruId !== null && $user->hasAnyRole(['guru'])
            ? DB::table('guru_rombel')->where('guru_id', $guruId)->pluck('rombel_id')
            : collect();

        $query->where(fn ($q) => $q->whereIn('kelas_id', $kelasIds)->orWhereIn('rombel_id', $rombelIds));
    }

    /** ID siswa (tabel siswa) yang menjadi peserta paket: rombel yang sama dan kelas asal yang sama. */
    private function siswaIdsPaket(PaketNilai $paket): Collection
    {
        return SiswaRombel::with('siswaKelas')->where('rombel_id', $paket->rombel_id)->get()
            ->filter(fn ($p) => (int) $p->siswaKelas->kelas_id === (int) $paket->kelas_id)
            ->map(fn ($p) => (int) $p->siswaKelas->siswa_id)
            ->values();
    }

    /**
     * Tambahkan status_fe dan periode_tutup pada respons (kolom status BE tidak diubah).
     * ASUMSI: 'periode-tutup' dipakai bila periode dikunci/lewat tanggal tutup dan paket belum
     * dikirim (draft/perlu_perbaikan), karena guru tidak dapat mengirim lagi.
     */
    private function tambahStatusFe(PaketNilai $paket): PaketNilai
    {
        $periode = $paket->periode;
        $tutup = $periode !== null && ($periode->dikunci || now()->greaterThan($periode->tanggal_tutup));

        $statusFe = self::STATUS_FE[$paket->status] ?? $paket->status;
        if ($tutup && in_array($paket->status, ['draft', 'perlu_perbaikan'], true)) {
            $statusFe = 'periode-tutup';
        }
        $paket->setAttribute('status_fe', $statusFe);
        $paket->setAttribute('periode_tutup', $tutup);

        return $paket;
    }
}