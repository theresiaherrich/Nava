<?php

namespace App\Services;

use App\Exceptions\AturanBisnisException;
use App\Models\Kelas;
use App\Models\PelengkapRapor;
use App\Models\PeriodeNilai;
use App\Models\PesertaEkskul;
use App\Models\Rapor;
use App\Models\RiwayatKelas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pelengkap rapor oleh wali kelas: presensi ringkas, catatan wali, kokurikuler.
 * Presensi berasal dari modul A1 (tabel `presensi`). Bila tabel belum ada, wali mengisi manual
 * dan status presensi ditampilkan `belum_tersedia`.
 */
class PelengkapService
{
    public function __construct(private AuditService $audit) {}

    public static function presensiA1Tersedia(): bool
    {
        return Schema::hasTable('presensi');
    }

    public function daftar(Kelas $kelas, PeriodeNilai $periode): array
    {
        $anggota = RiwayatKelas::with('siswa:id_siswa,nis,nama')->where('id_kelas', $kelas->id_kelas)->where('id_semester', $periode->id_semester)->get()
            ->sortBy(fn ($a) => mb_strtolower($a->siswa->nama))->values();
        $pelengkap = PelengkapRapor::where('id_periode', $periode->id_periode)->whereIn('id_riwayat_kelas', $anggota->pluck('id_riwayat_kelas'))->get()->keyBy('id_riwayat_kelas');
        $ekskul = PesertaEkskul::with('ekskul:id_ekskul,nama')->whereIn('id_riwayat_kelas', $anggota->pluck('id_riwayat_kelas'))->get()->groupBy('id_riwayat_kelas');

        return [
            'status_presensi_a1' => self::presensiA1Tersedia() ? 'tersedia' : 'belum_tersedia',
            'ekskul_ditampilkan' => $periode->jenis === 'SAS',
            'siswa' => $anggota->map(function (RiwayatKelas $a) use ($pelengkap, $ekskul) {
                $p = $pelengkap->get($a->id_riwayat_kelas);

                return [
                    'id_riwayat_kelas' => $a->id_riwayat_kelas,
                    'siswa' => $a->siswa->only(['id_siswa', 'nis', 'nama']),
                    'sakit' => $p?->sakit, 'izin' => $p?->izin, 'alpha' => $p?->alpha,
                    'sumber_presensi' => $p?->sumber_presensi,
                    'catatan_wali' => $p?->catatan_wali,
                    'kokurikuler' => $p?->kokurikuler,
                    'ekskul' => $ekskul->get($a->id_riwayat_kelas, collect())->map(fn ($e) => ['nama' => $e->ekskul->nama, 'predikat' => $e->predikat])->values(),
                    'terakhir_disimpan' => $p?->updated_at?->toIso8601String(),
                ];
            })->values(),
        ];
    }

    /** @param list<array{id_riwayat_kelas:int, sakit?:?int, izin?:?int, alpha?:?int, catatan_wali?:?string, kokurikuler?:?string}> $items */
    public function simpan(Kelas $kelas, PeriodeNilai $periode, array $items): int
    {
        $this->pastikanBisaDiubah($kelas, $periode);
        $idAnggota = RiwayatKelas::where('id_kelas', $kelas->id_kelas)->where('id_semester', $periode->id_semester)->pluck('id_riwayat_kelas')->flip();

        $errors = [];
        foreach ($items as $i => $item) {
            if (! $idAnggota->has($item['id_riwayat_kelas'])) {
                $errors["items.{$i}.id_riwayat_kelas"] = ['Siswa bukan anggota kelas ini.'];
            }
        }
        if ($errors) {
            throw new AturanBisnisException('Sebagian data tidak valid.', 'VALIDATION_ERROR', 422, $errors);
        }

        return DB::transaction(function () use ($periode, $items, $kelas) {
            foreach ($items as $item) {
                $data = array_intersect_key($item, array_flip(['sakit', 'izin', 'alpha', 'catatan_wali', 'kokurikuler']));
                if (array_intersect_key($data, array_flip(['sakit', 'izin', 'alpha'])) !== []) {
                    $data['sumber_presensi'] = 'manual';
                }
                PelengkapRapor::updateOrCreate(
                    ['id_riwayat_kelas' => $item['id_riwayat_kelas'], 'id_periode' => $periode->id_periode],
                    $data + ['diisi_oleh' => Auth::id()],
                );
            }
            $this->audit->catat('simpan_pelengkap_rapor', 'kelas', $kelas->id_kelas, null, ['id_periode' => $periode->id_periode, 'jumlah' => count($items)]);

            return count($items);
        });
    }

    /** Ambil ringkasan sakit/izin/alpa dari tabel presensi A1 untuk rentang tanggal. */
    public function tarikPresensi(Kelas $kelas, PeriodeNilai $periode, string $dari, string $sampai): int
    {
        if (! self::presensiA1Tersedia()) {
            throw new AturanBisnisException('Data presensi modul A1 belum tersedia. Isi presensi secara manual.', 'PRESENSI_A1_UNAVAILABLE', 409);
        }
        $this->pastikanBisaDiubah($kelas, $periode);

        $anggota = RiwayatKelas::where('id_kelas', $kelas->id_kelas)->where('id_semester', $periode->id_semester)->get();
        $rekap = DB::table('presensi')->whereIn('id_siswa', $anggota->pluck('id_siswa'))->whereBetween('tanggal', [$dari, $sampai])
            ->selectRaw("id_siswa, SUM(CASE WHEN status='Sakit' THEN 1 ELSE 0 END) sakit, SUM(CASE WHEN status='Izin' THEN 1 ELSE 0 END) izin, SUM(CASE WHEN status='Alpa' THEN 1 ELSE 0 END) alpha")
            ->groupBy('id_siswa')->get()->keyBy('id_siswa');

        return DB::transaction(function () use ($anggota, $rekap, $periode, $kelas, $dari, $sampai) {
            foreach ($anggota as $a) {
                $r = $rekap->get($a->id_siswa);
                PelengkapRapor::updateOrCreate(
                    ['id_riwayat_kelas' => $a->id_riwayat_kelas, 'id_periode' => $periode->id_periode],
                    ['sakit' => (int) ($r->sakit ?? 0), 'izin' => (int) ($r->izin ?? 0), 'alpha' => (int) ($r->alpha ?? 0), 'sumber_presensi' => 'a1', 'diisi_oleh' => Auth::id()],
                );
            }
            $this->audit->catat('tarik_presensi_a1', 'kelas', $kelas->id_kelas, null, ['id_periode' => $periode->id_periode, 'dari' => $dari, 'sampai' => $sampai]);

            return $anggota->count();
        });
    }

    /** Pelengkap ikut terkunci bila rapor kelas sudah terbit. */
    private function pastikanBisaDiubah(Kelas $kelas, PeriodeNilai $periode): void
    {
        $terbit = Rapor::where('id_periode', $periode->id_periode)->where('status', 'terbit')
            ->whereHas('riwayatKelas', fn ($q) => $q->where('id_kelas', $kelas->id_kelas))->exists();
        if ($terbit) {
            throw AturanBisnisException::terkunci('Rapor kelas ini sudah terbit; pelengkap rapor tidak dapat diubah.');
        }
    }
}
