<?php

namespace App\Services;

use App\Exceptions\AturanBisnisException;
use App\Models\Kelas;
use App\Models\PaketNilai;
use App\Models\PeriodeNilai;
use App\Models\Rombel;
use App\Models\VerifikasiNilai;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Alur paket nilai (pengajuan_nilai):
 *   draf ──kirim(guru)──▶ terkirim ──setujui(wali)──▶ disetujui ──kunci(kurikulum)──▶ dikunci
 *                          │  ▲                         │
 *                 minta_revisi│  └──── kirim ulang ◀─── perlu_revisi ◀── buka_koreksi(kurikulum)
 * Riwayat keputusan disimpan append-only di verifikasi_nilai.
 */
class PaketService
{
    public function __construct(
        private NilaiService $nilai,
        private AuditService $audit,
        private NotifikasiService $notif,
    ) {}

    /** @return array{paket: PaketNilai, kelengkapan: array} */
    public function kirim(Rombel $rombel, PeriodeNilai $periode, Kelas $kelas, bool $konfirmasiBelumLengkap): array
    {
        if ((int) $periode->id_semester !== (int) $rombel->id_semester || ! $periode->ikutTingkat($kelas->tingkat)) {
            throw new AturanBisnisException('Periode atau tingkat kelas tidak sesuai dengan rombel.');
        }

        return DB::transaction(function () use ($rombel, $periode, $kelas, $konfirmasiBelumLengkap) {
            Rombel::whereKey($rombel->id_rombel)->lockForUpdate()->first();
            $periode = PeriodeNilai::whereKey($periode->id_periode)->lockForUpdate()->firstOrFail();
            $paket = PaketNilai::firstOrCreate(
                ['id_rombel' => $rombel->id_rombel, 'id_periode' => $periode->id_periode, 'id_kelas' => $kelas->id_kelas],
                ['status' => 'draf'],
            );
            $paket = PaketNilai::whereKey($paket->id_paket_nilai)->lockForUpdate()->first();

            if (! $paket->bisaDiedit()) {
                throw new AturanBisnisException("Nilai berstatus {$paket->status}; tidak dapat dikirim ulang.", 'PAKET_STATUS');
            }
            if ($periode->status === 'dikunci' || (! $periode->terbukaUntukInput() && ! $paket->koreksi)) {
                throw AturanBisnisException::terkunci('Periode penilaian sedang ditutup atau dikunci.');
            }

            $lengkap = $this->nilai->kelengkapan($rombel, $periode, $kelas->id_kelas);
            if ($lengkap['jumlah_siswa'] === 0) {
                throw new AturanBisnisException('Tidak ada peserta dari kelas ini pada rombel.', 'PAKET_KOSONG');
            }
            if ($lengkap['jumlah_tp'] === 0) {
                throw new AturanBisnisException('Belum ada TP yang dinilai untuk periode ini.', 'PAKET_KOSONG');
            }
            if (! $lengkap['lengkap'] && ! $konfirmasiBelumLengkap) {
                throw new AturanBisnisException(
                    "Nilai belum lengkap ({$lengkap['sel_terisi']}/{$lengkap['sel_wajib']} sel). Kirim ulang dengan konfirmasi bila tetap ingin dikirim.",
                    'PAKET_BELUM_LENGKAP', 422, ['konfirmasi_belum_lengkap' => ['Konfirmasi diperlukan.']],
                );
            }

            $sebelum = $paket->status;
            $paket->update(['status' => 'terkirim', 'lengkap' => $lengkap['lengkap'], 'dikirim_oleh' => Auth::id(), 'waktu_kirim' => now()]);
            VerifikasiNilai::create(['id_paket_nilai' => $paket->id_paket_nilai, 'id_user' => Auth::id(), 'aksi' => 'kirim',
                'catatan' => $lengkap['lengkap'] ? null : "Dikirim belum lengkap ({$lengkap['sel_terisi']}/{$lengkap['sel_wajib']})."]);

            $this->audit->catat('kirim_nilai', 'paket_nilai', $paket->id_paket_nilai, ['status' => $sebelum], ['status' => 'terkirim', 'lengkap' => $lengkap['lengkap']]);
            $this->notif->kirim($this->notif->userWaliKelas($kelas->id_kelas, $rombel->id_semester), 'nilai_dikirim',
                "Nilai {$rombel->mapel->nama} dikirim",
                "Nilai {$periode->jenis} {$rombel->nama} untuk kelas {$kelas->nama} menunggu verifikasi Anda.",
                ['id_paket_nilai' => $paket->id_paket_nilai]);

            return ['paket' => $paket->fresh(), 'kelengkapan' => $lengkap];
        });
    }

    public function setujui(PaketNilai $paket, ?string $catatan = null): PaketNilai
    {
        return $this->putuskan($paket, ['terkirim'], 'disetujui', 'setujui', $catatan, 'nilai_disetujui', 'disetujui wali kelas');
    }

    public function mintaRevisi(PaketNilai $paket, string $catatan): PaketNilai
    {
        return $this->putuskan($paket, ['terkirim', 'disetujui'], 'perlu_revisi', 'minta_revisi', $catatan, 'nilai_perlu_revisi', 'perlu direvisi');
    }

    private function putuskan(PaketNilai $paket, array $dari, string $ke, string $aksi, ?string $catatan, string $jenisNotif, string $frasa): PaketNilai
    {
        return DB::transaction(function () use ($paket, $dari, $ke, $aksi, $catatan, $jenisNotif, $frasa) {
            Rombel::whereKey($paket->id_rombel)->lockForUpdate()->first();
            $paket = PaketNilai::with(['rombel.mapel', 'kelas', 'periode'])->lockForUpdate()->findOrFail($paket->id_paket_nilai);

            if (! in_array($paket->status, $dari, true)) {
                throw new AturanBisnisException("Nilai berstatus {$paket->status}; aksi ini tidak dapat dilakukan.", 'PAKET_STATUS');
            }

            $sebelum = $paket->status;
            $paket->update(['status' => $ke, 'diverifikasi_oleh' => Auth::id(), 'waktu_verifikasi' => now()]);
            VerifikasiNilai::create(['id_paket_nilai' => $paket->id_paket_nilai, 'id_user' => Auth::id(), 'aksi' => $aksi, 'catatan' => $catatan]);

            $this->audit->catat($aksi === 'minta_revisi' ? 'minta_revisi' : 'setujui_nilai', 'paket_nilai', $paket->id_paket_nilai,
                ['status' => $sebelum], ['status' => $ke], $catatan);
            $this->notif->kirim($this->notif->userGuruRombel($paket->rombel), $jenisNotif,
                "Nilai {$paket->rombel->mapel->nama} {$frasa}",
                "Nilai {$paket->periode->jenis} {$paket->rombel->nama} kelas {$paket->kelas->nama} {$frasa}.".($catatan ? " Catatan: {$catatan}" : ''),
                ['id_paket_nilai' => $paket->id_paket_nilai]);

            return $paket->fresh();
        });
    }
}
