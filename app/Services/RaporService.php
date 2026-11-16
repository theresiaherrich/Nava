<?php

namespace App\Services;

use App\Exceptions\AturanBisnisException;
use App\Models\Kelas;
use App\Models\PaketNilai;
use App\Models\PelengkapRapor;
use App\Models\PeriodeNilai;
use App\Models\PesertaEkskul;
use App\Models\Rapor;
use App\Models\RiwayatKelas;
use App\Models\Sekolah;
use App\Models\SiswaRombel;
use App\Models\TemplateRapor;
use App\Models\Tp;
use App\Models\WaliKelas;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * Rapor = snapshot `data_final` + PDF di disk privat. Data berubah (koreksi) → versi baru;
 * versi lama tidak pernah ditimpa. Siswa/orang tua hanya bisa mengunduh versi berstatus `terbit`.
 */
class RaporService
{
    public function __construct(
        private NilaiService $nilai,
        private AuditService $audit,
        private NotifikasiService $notif,
    ) {}

    /** @return array{dikunci: bool, jumlah_mapel: int, belum_dikunci: list<string>} */
    public function statusKelas(Kelas $kelas, PeriodeNilai $periode): array
    {
        $rombel = SiswaRombel::join('riwayat_kelas as rk', 'rk.id_riwayat_kelas', '=', 'siswa_rombel.id_riwayat_kelas')
            ->where('rk.id_kelas', $kelas->id_kelas)->where('rk.id_semester', $periode->id_semester)
            ->distinct()->pluck('siswa_rombel.id_rombel');
        $paket = PaketNilai::with('rombel:id_rombel,nama')->where('id_periode', $periode->id_periode)->where('id_kelas', $kelas->id_kelas)
            ->whereIn('id_rombel', $rombel)->get()->keyBy('id_rombel');

        $belum = $rombel->filter(fn ($id) => ($paket->get($id)?->status) !== 'dikunci')
            ->map(fn ($id) => $paket->get($id)?->rombel?->nama ?? "Rombel #{$id}")->values()->all();

        return ['dikunci' => $rombel->isNotEmpty() && $belum === [], 'jumlah_mapel' => $rombel->count(), 'belum_dikunci' => $belum];
    }

    public function pastikanBisaCetak(Kelas $kelas, PeriodeNilai $periode): TemplateRapor
    {
        if ((int) $kelas->id_tahun_ajaran !== (int) $periode->semester->id_tahun_ajaran || ! $periode->ikutTingkat($kelas->tingkat)) {
            throw new AturanBisnisException('Kelas tidak ikut periode ini.');
        }
        $status = $this->statusKelas($kelas, $periode);
        if (! $status['dikunci']) {
            throw AturanBisnisException::terkunci($status['jumlah_mapel'] === 0
                ? 'Belum ada nilai mapel untuk kelas ini.'
                : 'Rapor hanya dapat dicetak setelah semua nilai dikunci. Belum dikunci: '.implode(', ', $status['belum_dikunci']).'.');
        }

        $template = TemplateRapor::aktifUntuk($periode->jenis)
            ?? throw new AturanBisnisException("Template rapor {$periode->jenis} yang aktif belum diatur Kurikulum.", 'TEMPLATE_MISSING');
        if (! Sekolah::find(1)) {
            throw new AturanBisnisException('Identitas sekolah belum diisi Operator.', 'SCHOOL_MISSING');
        }

        return $template;
    }

    /** Bentuk snapshot data rapor satu siswa. */
    public function bangun(RiwayatKelas $anggota, PeriodeNilai $periode, TemplateRapor $template): array
    {
        $anggota->loadMissing(['siswa', 'kelas', 'semester.tahunAjaran']);
        $sas = $periode->jenis === 'SAS';
        $idSts = $sas ? PeriodeNilai::where('id_semester', $periode->id_semester)->where('jenis', 'STS')->value('id_periode') : $periode->id_periode;

        $peserta = SiswaRombel::with(['rombel.mapel', 'rombel.tp'])->where('id_riwayat_kelas', $anggota->id_riwayat_kelas)->get();
        $nilai = $this->nilai->nilaiPeserta($peserta->pluck('id_siswa_rombel'));

        $mapel = $peserta->map(function (SiswaRombel $p) use ($nilai, $idSts, $sas, $template) {
            $m = $p->rombel->mapel;
            $n = $nilai->get($p->id_siswa_rombel, collect());
            $h = NilaiService::hitung($p->rombel->tp, $n, $idSts ? (int) $idSts : null);

            return [
                'kode' => $m->kode,
                'mapel' => $m->nama,
                'kelompok' => $m->kelompok ?: 'Umum',
                'urutan' => $m->urutan_rapor,
                'nilai' => $sas ? $h['nilai_akhir'] : $h['nilai_sts'],
                'nilai_sas' => $sas ? $h['nilai_sas'] : null,
                'deskripsi' => $sas && $template->tampilkan_deskripsi ? $this->deskripsi($p->rombel->tp, $n, $m) : null,
            ];
        })->sortBy(fn ($m) => [$m['urutan'], $m['mapel']])->values()->all();

        $pelengkap = PelengkapRapor::where('id_riwayat_kelas', $anggota->id_riwayat_kelas)->where('id_periode', $periode->id_periode)->first();
        $wali = WaliKelas::with('guru:id_guru,nama,nip')->where('id_kelas', $anggota->id_kelas)->where('id_semester', $anggota->id_semester)->first();
        $sekolah = Sekolah::find(1);

        return [
            'jenis' => $periode->jenis,
            'sekolah' => $sekolah?->only(['nama', 'npsn', 'alamat', 'kota', 'kepala_sekolah', 'nip_kepala_sekolah']),
            'siswa' => $anggota->siswa->only(['id_siswa', 'nis', 'nisn', 'nama']),
            'kelas' => $anggota->kelas->only(['id_kelas', 'nama', 'tingkat', 'fase']),
            'semester' => ['nama' => $anggota->semester->nama, 'tahun_ajaran' => $anggota->semester->tahunAjaran->nama],
            'mapel' => $mapel,
            'ekskul' => $sas ? PesertaEkskul::with('ekskul:id_ekskul,nama')->where('id_riwayat_kelas', $anggota->id_riwayat_kelas)->whereNotNull('predikat')->get()
                ->map(fn ($e) => ['nama' => $e->ekskul->nama, 'predikat' => $e->predikat, 'catatan' => $e->catatan])->sortBy('nama')->values()->all() : [],
            'kehadiran' => ['sakit' => $pelengkap?->sakit, 'izin' => $pelengkap?->izin, 'alpha' => $pelengkap?->alpha],
            'catatan_wali' => $pelengkap?->catatan_wali,
            'kokurikuler' => $pelengkap?->kokurikuler,
            'wali_kelas' => $wali?->guru?->only(['nama', 'nip']),
            'judul' => $template->judul,
            'tempat_cetak' => $template->tempat_cetak ?: $sekolah?->kota,
        ];
    }

    /** @return array{0: Rapor, 1: bool} [rapor, versiBaru] */
    public function cetak(RiwayatKelas $anggota, PeriodeNilai $periode, TemplateRapor $template): array
    {
        $data = $this->normal($this->bangun($anggota, $periode, $template));

        return DB::transaction(function () use ($anggota, $periode, $template, $data) {
            RiwayatKelas::whereKey($anggota->id_riwayat_kelas)->lockForUpdate()->first();
            $terakhir = Rapor::where('id_riwayat_kelas', $anggota->id_riwayat_kelas)->where('id_periode', $periode->id_periode)
                ->orderByDesc('versi')->first();

            if ($terakhir && $terakhir->status !== 'dicabut' && (int) $terakhir->id_template === (int) $template->id_template
                && $terakhir->data_final == $data && $this->disk()->exists($terakhir->file_pdf)) {
                return [$terakhir, false];
            }

            $versi = ($terakhir?->versi ?? 0) + 1;
            $path = "rapor/{$periode->id_periode}/{$anggota->id_kelas}/".Str::slug($data['siswa']['nis'])."-v{$versi}.pdf";
            $this->disk()->put($path, $this->render([$data + ['versi' => $versi]], $template));

            $rapor = Rapor::create([
                'id_riwayat_kelas' => $anggota->id_riwayat_kelas, 'id_periode' => $periode->id_periode,
                'id_template' => $template->id_template, 'versi' => $versi, 'data_final' => $data,
                'file_pdf' => $path, 'status' => 'draf', 'dicetak_oleh' => Auth::id(),
            ]);
            $this->audit->catat('cetak_rapor', 'rapor', $rapor->id_rapor, null, ['versi' => $versi, 'id_riwayat_kelas' => $anggota->id_riwayat_kelas, 'id_periode' => $periode->id_periode]);

            return [$rapor, true];
        });
    }

    /** Cetak semua siswa kelas → isi file (PDF gabungan atau ZIP per siswa). */
    public function cetakKelas(Kelas $kelas, PeriodeNilai $periode, string $format): array
    {
        $template = $this->pastikanBisaCetak($kelas, $periode);
        $anggota = RiwayatKelas::with('siswa')->where('id_kelas', $kelas->id_kelas)->where('id_semester', $periode->id_semester)->get()
            ->sortBy(fn ($a) => mb_strtolower($a->siswa->nama))->values();

        $rapor = $anggota->map(fn ($a) => $this->cetak($a, $periode, $template)[0]);
        $nama = Str::slug("rapor-{$periode->jenis}-{$kelas->nama}");
        $this->audit->catat('cetak_rapor_kelas', 'kelas', $kelas->id_kelas, null, ['id_periode' => $periode->id_periode, 'jumlah' => $rapor->count(), 'format' => $format]);

        if ($format === 'zip') {
            $tmp = tempnam(sys_get_temp_dir(), 'rapor');
            $zip = new ZipArchive;
            $zip->open($tmp, ZipArchive::OVERWRITE);
            foreach ($rapor as $r) {
                $zip->addFromString(basename($r->file_pdf), $this->disk()->get($r->file_pdf));
            }
            $zip->close();
            $isi = file_get_contents($tmp);
            @unlink($tmp);

            return ['isi' => $isi, 'nama' => "{$nama}.zip", 'mime' => 'application/zip'];
        }

        return ['isi' => $this->render($rapor->map(fn ($r) => $r->data_final + ['versi' => $r->versi])->all(), $template), 'nama' => "{$nama}.pdf", 'mime' => 'application/pdf'];
    }

    /** Terbitkan versi terbaru setiap siswa kelas (Kurikulum). @return int jumlah terbit */
    public function terbitkan(Kelas $kelas, PeriodeNilai $periode): int
    {
        return DB::transaction(function () use ($kelas, $periode) {
            $terbaru = Rapor::with('riwayatKelas.siswa.orangTua')->where('id_periode', $periode->id_periode)
                ->whereHas('riwayatKelas', fn ($q) => $q->where('id_kelas', $kelas->id_kelas))
                ->lockForUpdate()->get()->groupBy('id_riwayat_kelas')->map(fn (Collection $g) => $g->sortByDesc('versi')->first());

            if ($terbaru->isEmpty()) {
                throw new AturanBisnisException('Belum ada rapor yang dicetak untuk kelas ini.', 'RAPOR_BELUM_DICETAK');
            }

            $n = 0;
            $penerima = [];
            foreach ($terbaru as $r) {
                if ($r->status !== 'draf') {
                    continue;
                }
                $r->update(['status' => 'terbit', 'diterbitkan_oleh' => Auth::id(), 'waktu_terbit' => now()]);
                $n++;
                $siswa = $r->riwayatKelas->siswa;
                $penerima = [...$penerima, $siswa->id_user, ...$siswa->orangTua->pluck('id_user')->all()];
            }

            $this->audit->catat('terbitkan_rapor', 'kelas', $kelas->id_kelas, null, ['id_periode' => $periode->id_periode, 'jumlah' => $n]);
            $this->notif->kirim(array_filter($penerima), 'rapor_terbit', "Rapor {$periode->jenis} terbit",
                "Rapor {$periode->jenis} kelas {$kelas->nama} sudah dapat diunduh.", ['id_periode' => $periode->id_periode]);

            return $n;
        });
    }

    public function cabut(Rapor $rapor, string $alasan): Rapor
    {
        if ($rapor->status !== 'terbit') {
            throw new AturanBisnisException('Hanya rapor terbit yang dapat dicabut.');
        }

        return DB::transaction(function () use ($rapor, $alasan) {
            $rapor->update(['status' => 'dicabut', 'alasan_cabut' => $alasan]);
            $this->audit->catat('cabut_rapor', 'rapor', $rapor->id_rapor, ['status' => 'terbit'], ['status' => 'dicabut'], $alasan);

            return $rapor->fresh();
        });
    }

    public function isiFile(Rapor $rapor): string
    {
        if (! $this->disk()->exists($rapor->file_pdf)) {
            throw new AturanBisnisException('Berkas rapor tidak ditemukan. Cetak ulang rapor.', 'FILE_MISSING', 404);
        }

        return $this->disk()->get($rapor->file_pdf);
    }

    /** @param list<array> $items */
    public function render(array $items, TemplateRapor $template): string
    {
        $view = array_key_exists($template->view, config('siakad.rapor_views')) ? $template->view : 'rapor.semester';

        return Pdf::loadView($view, ['items' => $items])->setPaper('a4')->output();
    }

    /** Deskripsi capaian: TP ≥ KKTP dengan nilai tertinggi (kuat) dan TP < KKTP terendah (penguatan). */
    private function deskripsi(Collection $tp, Collection $nilai, $mapel): ?string
    {
        $terisi = $tp->map(fn (Tp $t) => ['tp' => $t, 'n' => $nilai->get($t->id_tp)?->nilai])->filter(fn ($x) => $x['n'] !== null);
        if ($terisi->isEmpty()) {
            return null;
        }
        $kuat = $terisi->filter(fn ($x) => $x['n'] >= $x['tp']->kktpEfektif($mapel))->sortByDesc('n')->take(2);
        $lemah = $terisi->filter(fn ($x) => $x['n'] < $x['tp']->kktpEfektif($mapel))->sortBy('n')->take(2);
        $teks = fn ($c) => $c->map(fn ($x) => Str::lcfirst(rtrim($x['tp']->deskripsi, '.')))->implode('; ');

        return trim(implode(' ', array_filter([
            $kuat->isNotEmpty() ? str_replace(':tp', $teks($kuat), $mapel->deskripsi_kuat ?: config('siakad.deskripsi_capaian.kuat')) : null,
            $lemah->isNotEmpty() ? str_replace(':tp', $teks($lemah), $mapel->deskripsi_penguatan ?: config('siakad.deskripsi_capaian.penguatan')) : null,
        ])));
    }

    /** Normalisasi lewat JSON agar perbandingan dengan data_final tersimpan konsisten. */
    private function normal(array $data): array
    {
        return json_decode(json_encode($data), true);
    }

    private function disk(): Filesystem
    {
        return Storage::disk(config('siakad.rapor_disk'));
    }
}
