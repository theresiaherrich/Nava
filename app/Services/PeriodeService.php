<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\PaketNilai;
use App\Models\PeriodeNilai;
use App\Models\WaliKelas;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Periode STS/SAS per tingkat, ringkasan kelengkapan per kelas, dan penguncian.
 * Penguncian TIDAK menerbitkan rapor; itu langkah terpisah (modul rapor).
 */
class PeriodeService
{
    public const STATUS_SIAP = ['disetujui', 'terkunci'];

    public function __construct(private AuditService $audit) {}

    public function buat(array $data): PeriodeNilai
    {
        $sudahAda = PeriodeNilai::where('id_semester', $data['id_semester'])
            ->where('tingkat', $data['tingkat'])
            ->where('jenis', $data['jenis'])
            ->exists();

        if ($sudahAda) {
            throw ValidationException::withMessages(['jenis' => 'Periode untuk tingkat dan jenis ini sudah ada.']);
        }

        return DB::transaction(function () use ($data) {
            $periode = PeriodeNilai::create($data + ['status' => 'draf']);

            $this->audit->catat('buat_periode', 'periode_nilai', $periode->id_periode, null, null,
                $periode->only(['id_semester', 'tingkat', 'jenis', 'tanggal_mulai', 'tanggal_selesai', 'status']));

            return $periode;
        });
    }

    public function ubahJadwal(PeriodeNilai $periode, string $mulai, string $selesai): PeriodeNilai
    {
        return DB::transaction(function () use ($periode, $mulai, $selesai) {
            $sebelum = $periode->only(['tanggal_mulai', 'tanggal_selesai']);

            $periode->update(['tanggal_mulai' => $mulai, 'tanggal_selesai' => $selesai]);

            $this->audit->catat('ubah_jadwal_periode', 'periode_nilai', $periode->id_periode, null, $sebelum,
                $periode->only(['tanggal_mulai', 'tanggal_selesai']));

            return $periode;
        });
    }

    public function ubahStatus(PeriodeNilai $periode, string $status): PeriodeNilai
    {
        return DB::transaction(function () use ($periode, $status) {
            $sebelum = ['status' => $periode->status];

            $periode->update(['status' => $status]);

            $this->audit->catat('ubah_status_periode', 'periode_nilai', $periode->id_periode, null, $sebelum, ['status' => $status]);

            return $periode;
        });
    }

    /**
     * Membentuk paket (rombel x kelas asal) untuk periode ini dari data peserta rombel.
     * Idempoten: paket yang sudah ada tidak diubah.
     */
    public function sinkronkanPaket(PeriodeNilai $periode): int
    {
        $pasangan = DB::table('siswa_rombel as sr')
            ->join('riwayat_kelas as rk', 'rk.id_riwayat_kelas', '=', 'sr.id_riwayat_kelas')
            ->join('kelas as k', 'k.id_kelas', '=', 'rk.id_kelas')
            ->join('rombel as r', 'r.id_rombel', '=', 'sr.id_rombel')
            ->where('rk.id_semester', $periode->id_semester)
            ->where('r.id_semester', $periode->id_semester)
            ->where('k.tingkat', $periode->tingkat)
            ->select('r.id_rombel', 'rk.id_kelas')
            ->distinct()
            ->get();

        $dibuat = 0;

        foreach ($pasangan as $p) {
            $paket = PaketNilai::firstOrCreate(
                ['id_periode' => $periode->id_periode, 'id_rombel' => $p->id_rombel, 'id_kelas' => $p->id_kelas],
                ['status' => 'draf'],
            );

            if ($paket->wasRecentlyCreated) {
                $dibuat++;
            }
        }

        return $dibuat;
    }

    /** @return array<int, array<string, mixed>> */
    public function ringkasan(PeriodeNilai $periode): array
    {
        $this->sinkronkanPaket($periode);

        $semester = $periode->semester()->with('tahunAjaran')->first();

        $kelasList = Kelas::where('id_tahun_ajaran', $semester->id_tahun_ajaran)
            ->where('tingkat', $periode->tingkat)
            ->orderBy('nama')
            ->get();

        $wali = WaliKelas::with('guru:id_guru,nama')
            ->where('id_semester', $periode->id_semester)
            ->get()
            ->keyBy('id_kelas');

        $paketPerKelas = PaketNilai::with('rombel.mapel')
            ->where('id_periode', $periode->id_periode)
            ->get()
            ->groupBy('id_kelas');

        return $kelasList->map(function (Kelas $kelas) use ($wali, $paketPerKelas) {
            $paket = $paketPerKelas->get($kelas->id_kelas, collect());

            return [
                'id_kelas' => $kelas->id_kelas,
                'kelas' => $kelas->nama,
                'wali_kelas' => $wali->get($kelas->id_kelas)?->guru?->nama,
                'mapel_siap' => $paket->filter(fn ($p) => in_array($p->status, self::STATUS_SIAP, true))->count(),
                'mapel_total' => $paket->count(),
                'status' => $this->statusKelas($paket),
                // Dipakai FE untuk detail per mapel dan tombol "Buka Koreksi" (id_paket_nilai).
                'paket' => $paket->map(fn ($p) => [
                    'id_paket_nilai' => $p->id_paket_nilai,
                    'mapel' => $p->rombel?->mapel?->nama,
                    'status' => $p->status,
                    'alasan_koreksi' => $p->alasan_koreksi,
                ])->values()->all(),
            ];
        })->values()->all();
    }

    /** Status agregat kelas dari status paket-paketnya. */
    public function statusKelas(Collection $paket): string
    {
        if ($paket->isEmpty()) {
            return 'belum_ada_mapel';
        }

        if ($paket->every(fn ($p) => $p->status === 'terkunci')) {
            return 'terkunci';
        }

        if ($paket->contains(fn ($p) => $p->status === 'revisi' && filled($p->alasan_koreksi))) {
            return 'dibuka_koreksi';
        }

        if ($paket->contains(fn ($p) => $p->status === 'revisi')) {
            return 'perlu_revisi';
        }

        if ($paket->contains(fn ($p) => $p->status === 'terkirim')) {
            return 'menunggu_verifikasi';
        }

        if ($paket->every(fn ($p) => in_array($p->status, self::STATUS_SIAP, true))) {
            return 'siap_dikunci';
        }

        return 'terbuka';
    }

    public function kunciKelas(PeriodeNilai $periode, Kelas $kelas): int
    {
        if ((string) $kelas->tingkat !== (string) $periode->tingkat) {
            throw ValidationException::withMessages(['kelas' => 'Tingkat kelas tidak sesuai dengan periode.']);
        }

        return DB::transaction(function () use ($periode, $kelas) {
            $paket = PaketNilai::where('id_periode', $periode->id_periode)
                ->where('id_kelas', $kelas->id_kelas)
                ->lockForUpdate()
                ->get();

            if ($paket->isEmpty()) {
                throw ValidationException::withMessages(['kelas' => 'Belum ada mapel pada kelas ini.']);
            }

            if ($paket->contains(fn ($p) => ! in_array($p->status, self::STATUS_SIAP, true))) {
                throw ValidationException::withMessages(['kelas' => 'Masih ada mapel yang belum disetujui.']);
            }

            $sebelum = $paket->countBy('status')->all();
            $pelaku = Auth::id();
            $sekarang = now();

            foreach ($paket as $p) {
                if ($p->status === 'disetujui') {
                    $p->update(['status' => 'terkunci', 'dikunci_oleh' => $pelaku, 'waktu_kunci' => $sekarang]);
                }
            }

            $this->audit->catat('kunci_nilai', 'periode_nilai', $periode->id_periode, null, $sebelum, [
                'kelas' => $kelas->nama,
                'jumlah_mapel' => $paket->count(),
                'status' => 'terkunci',
            ]);

            $this->tutupPeriodeBilaSelesai($periode);

            return $paket->count();
        });
    }

    /** @return array<int, string> nama kelas yang berhasil dikunci */
    public function kunciSemuaSiap(PeriodeNilai $periode): array
    {
        $terkunci = [];

        foreach ($this->ringkasan($periode) as $baris) {
            if ($baris['status'] !== 'siap_dikunci') {
                continue;
            }

            $this->kunciKelas($periode, Kelas::findOrFail($baris['id_kelas']));
            $terkunci[] = $baris['kelas'];
        }

        return $terkunci;
    }

    /** Membuka koreksi satu paket terkunci. Wajib beralasan; nilai final lama tetap ada di log. */
    public function bukaKoreksi(PaketNilai $paket, string $alasan): PaketNilai
    {
        return DB::transaction(function () use ($paket, $alasan) {
            $paket = PaketNilai::lockForUpdate()->findOrFail($paket->id_paket_nilai);

            if ($paket->status !== 'terkunci') {
                throw ValidationException::withMessages(['paket' => 'Hanya mapel yang sudah terkunci yang bisa dibuka koreksinya.']);
            }

            $sebelum = ['status' => $paket->status, 'alasan_koreksi' => $paket->alasan_koreksi];

            $paket->update(['status' => 'revisi', 'alasan_koreksi' => $alasan]);

            $periode = $paket->periode;
            if ($periode->status === 'terkunci') {
                $periode->update(['status' => 'ditutup']);
            }

            $this->audit->catat('buka_koreksi', 'paket_nilai', $paket->id_paket_nilai, $alasan, $sebelum,
                ['status' => 'revisi', 'alasan_koreksi' => $alasan]);

            return $paket->fresh();
        });
    }

    private function tutupPeriodeBilaSelesai(PeriodeNilai $periode): void
    {
        $masihTerbuka = PaketNilai::where('id_periode', $periode->id_periode)
            ->where('status', '!=', 'terkunci')
            ->exists();

        if (! $masihTerbuka) {
            $periode->update(['status' => 'terkunci']);
        }
    }
}
