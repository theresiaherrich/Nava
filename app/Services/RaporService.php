<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\NilaiTp;
use App\Models\PaketNilai;
use App\Models\PelengkapRapor;
use App\Models\PeriodeNilai;
use App\Models\PesertaEkskul;
use App\Models\Rapor;
use App\Models\Sekolah;
use App\Models\SiswaKelas;
use App\Models\SiswaRombel;
use App\Models\TemplateRapor;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Pembentuk data rapor dan pengarsip versinya.
 * Sumber data: nilai_tp (TP wajib periode, via PenilaianAccess::tpDapatDiisi), paket_nilai (status dikunci),
 * pelengkap_rapor, peserta_ekskul. Nilai akhir mapel = rata-rata skor TP wajib, 2 desimal (ASUMSI: panduan 7.1 belum final).
 */
class RaporService
{
    public const TINGKAT_ROMAWI = [10 => 'X', 11 => 'XI', 12 => 'XII'];

    /** Periode harus satu semester dan satu tingkat dengan kelas. */
    public static function pastikanPeriodeCocok(Kelas $kelas, PeriodeNilai $periode): void
    {
        abort_if((int) $kelas->semester_id !== (int) $periode->semester_id, 422, 'Periode dan kelas berasal dari semester berbeda.');
        abort_if((int) $kelas->tingkat !== (int) $periode->tingkat, 422, 'Tingkat kelas tidak sesuai dengan periode.');
    }

    /** Peserta kelas pada semester kelas tersebut, urut nama. */
    public static function siswaKelas(Kelas $kelas): Collection
    {
        return SiswaKelas::with('siswa')
            ->where('kelas_id', $kelas->id)
            ->where('semester_id', $kelas->semester_id)
            ->get()
            ->sortBy(fn (SiswaKelas $sk) => mb_strtolower($sk->siswa->nama))
            ->values();
    }

    /**
     * Bangun data rapor seluruh siswa kelas (query dikelompokkan, tanpa N+1).
     *
     * @return array{dikunci: bool, jumlah_mapel: int, mapel_belum_dikunci: int, data: Collection} data dikunci per siswa_kelas.id
     */
    public static function bangun(Kelas $kelas, PeriodeNilai $periode): array
    {
        $kelas->loadMissing('semester.tahunAjaran');
        $semua = self::siswaKelas($kelas);
        $skIds = $semua->pluck('id');

        $peserta = SiswaRombel::with(['rombel.mapel', 'rombel.tp'])->whereIn('siswa_kelas_id', $skIds)->get();
        $rombelIds = $peserta->pluck('rombel_id')->unique()->values();

        $statusPaket = PaketNilai::where('periode_id', $periode->id)
            ->where('kelas_id', $kelas->id)
            ->whereIn('rombel_id', $rombelIds)
            ->pluck('status', 'rombel_id');
        $belum = $rombelIds->filter(fn ($id) => ($statusPaket[$id] ?? null) !== 'dikunci')->count();

        $nilai = NilaiTp::whereIn('siswa_rombel_id', $peserta->pluck('id'))->get()
            ->groupBy('siswa_rombel_id')
            ->map(fn ($grup) => $grup->keyBy('tp_id'));
        $pelengkap = PelengkapRapor::where('id_periode', $periode->id)->whereIn('id_riwayat_kelas', $skIds)->get()->keyBy('id_riwayat_kelas');
        $ekskul = PesertaEkskul::with(['ekskul', 'predikat'])
            ->where('id_semester', $kelas->semester_id)
            ->whereIn('id_siswa', $semua->pluck('siswa_id'))
            ->get()
            ->groupBy('id_siswa');
        $pesertaPerSk = $peserta->groupBy('siswa_kelas_id');

        $data = $semua->mapWithKeys(function (SiswaKelas $sk) use ($kelas, $periode, $pesertaPerSk, $nilai, $pelengkap, $ekskul) {
            $mapel = ($pesertaPerSk->get($sk->id) ?? collect())->map(function (SiswaRombel $sr) use ($periode, $nilai) {
                $tpWajib = $sr->rombel->tp->filter(fn ($tp) => PenilaianAccess::tpDapatDiisi($periode, $tp));
                $skor = $tpWajib
                    ->map(fn ($tp) => $nilai->get($sr->id)?->get($tp->id)?->skor)
                    ->filter(fn ($s) => $s !== null)
                    ->map(fn ($s) => (float) $s);

                return [
                    'mapel' => $sr->rombel->mapel->nama,
                    'kode' => $sr->rombel->mapel->kode,
                    'rombel' => $sr->rombel->nama,
                    // null = belum ada nilai, BUKAN 0.
                    'nilai_akhir' => $skor->isEmpty() ? null : round($skor->avg(), 2),
                    'jumlah_tp' => $tpWajib->count(),
                    'jumlah_tp_terisi' => $skor->count(),
                ];
            })->sortBy('mapel')->values()->all();

            $p = $pelengkap->get($sk->id);
            $kehadiran = [
                'sakit' => $p?->sakit, 'izin' => $p?->izin, 'alpha' => $p?->alpha,
            ];
            $kehadiran['tersedia'] = $kehadiran['sakit'] !== null && $kehadiran['izin'] !== null && $kehadiran['alpha'] !== null;

            return [$sk->id => [
                'siswa' => ['id' => $sk->siswa_id, 'nis' => $sk->siswa->nis, 'nisn' => $sk->siswa->nisn, 'nama' => $sk->siswa->nama],
                'kelas' => ['id' => $kelas->id, 'nama' => $kelas->nama, 'tingkat' => (int) $kelas->tingkat, 'tingkat_label' => self::TINGKAT_ROMAWI[(int) $kelas->tingkat] ?? (string) $kelas->tingkat],
                'semester' => ['id' => $kelas->semester_id, 'nama' => $kelas->semester->nama, 'tahun_ajaran' => $kelas->semester->tahunAjaran?->nama],
                'periode' => ['id' => $periode->id, 'jenis' => $periode->jenis, 'tingkat' => (int) $periode->tingkat],
                'mapel' => $mapel,
                'ekskul' => ($ekskul->get($sk->siswa_id) ?? collect())
                    ->map(fn (PesertaEkskul $e) => ['nama' => $e->ekskul->nama, 'predikat' => $e->predikat->predikat, 'catatan' => $e->catatan])
                    ->sortBy('nama')->values()->all(),
                'kehadiran' => $kehadiran,
                'catatan_wali' => $p?->catatan_wali,
                'kokurikuler' => $p?->kokurikuler,
            ]];
        });

        return [
            'dikunci' => $rombelIds->isNotEmpty() && $belum === 0,
            'jumlah_mapel' => $rombelIds->count(),
            'mapel_belum_dikunci' => $belum,
            'data' => $data,
        ];
    }

    /** Template sesuai jenis periode; tanpa jenis yang cocok, pakai template umum (jenis null). Tidak ada tebakan lain. */
    public static function cariTemplate(PeriodeNilai $periode): ?TemplateRapor
    {
        $templates = TemplateRapor::where('id_sekolah', 1)->orderByDesc('id_template')->get();

        return $templates->first(fn ($t) => $t->jenis !== null && strcasecmp($t->jenis, $periode->jenis) === 0)
            ?? $templates->first(fn ($t) => $t->jenis === null || $t->jenis === '');
    }

    /** Syarat cetak: semua mapel kelas dikunci, template ada. Mengembalikan template. */
    public static function pastikanBisaCetak(array $hasil, PeriodeNilai $periode): TemplateRapor
    {
        abort_unless($hasil['dikunci'], 422, $hasil['jumlah_mapel'] === 0
            ? 'Belum ada nilai rombel untuk kelas ini. Rapor belum dapat dicetak.'
            : "Rapor belum dapat dicetak: {$hasil['mapel_belum_dikunci']} mapel belum dikunci Kurikulum.");

        $template = self::cariTemplate($periode);
        abort_if($template === null, 422, "Template rapor untuk periode {$periode->jenis} belum tersedia di tabel template_rapor.");
        abort_if(Sekolah::find(1) === null, 422, 'Identitas sekolah belum diisi.');

        return $template;
    }

    /**
     * Arsipkan rapor satu siswa. Data final identik dengan versi terakhir (dan file masih ada) memakai ulang
     * versi itu; data berubah (mis. koreksi nilai/catatan) membuat versi baru v2, v3, dst. Versi lama tidak ditimpa.
     *
     * @return array{0: Rapor, 1: bool} [rapor, versiBaru]
     */
    public static function arsipkan(int $siswaKelasId, PeriodeNilai $periode, TemplateRapor $template, array $data, User $user): array
    {
        $disk = Storage::disk('local');
        $normal = json_decode(json_encode($data), true);

        return DB::transaction(function () use ($siswaKelasId, $periode, $template, $data, $normal, $user, $disk) {
            $riwayat = Rapor::where('id_riwayat_kelas', $siswaKelasId)->where('id_periode', $periode->id);
            $terakhir = (clone $riwayat)->lockForUpdate()->orderByDesc('id_rapor')->first();

            if ($terakhir !== null
                && (int) $terakhir->id_template === (int) $template->id_template
                && $terakhir->data_final == $normal
                && $terakhir->file_pdf !== null
                && $disk->exists($terakhir->file_pdf)) {
                return [$terakhir, false];
            }

            $versi = 'v'.((clone $riwayat)->count() + 1);
            $path = "rapor/periode-{$periode->id}/kelas-{$data['kelas']['id']}/siswa-{$data['siswa']['id']}_{$versi}.pdf";
            $disk->put($path, self::renderPdf([['data' => $data, 'versi' => $versi]], $template));

            $rapor = Rapor::create([
                'id_riwayat_kelas' => $siswaKelasId,
                'id_periode' => $periode->id,
                'id_template' => $template->id_template,
                'versi' => $versi,
                'data_final' => $normal,
                'file_pdf' => $path,
                'tanggal_cetak' => now()->toDateString(),
                // ASUMSI: PDF yang sudah dibuat berstatus 'siap'. 'terbit' (dibagikan ke orang tua) bukan bagian 4.8.
                'status' => 'siap',
                'diterbitkan_oleh' => $user->id,
            ]);
            AuditService::record('cetak_rapor', 'rapor', $rapor->id_rapor, null, ['versi' => $versi, 'id_riwayat_kelas' => $siswaKelasId, 'id_periode' => $periode->id], null, $user->id);

            return [$rapor, true];
        });
    }

    /** @param array<int, array{data: array, versi: string}> $items */
    public static function renderPdf(array $items, TemplateRapor $template): string
    {
        return Pdf::loadView('rapor.pdf', [
            'items' => $items,
            'template' => $template,
            'sekolah' => Sekolah::find(1),
        ])->setPaper('a4')->output();
    }
}
