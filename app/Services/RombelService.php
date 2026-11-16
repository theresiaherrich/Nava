<?php

namespace App\Services;

use App\Exceptions\AturanBisnisException;
use App\Models\NilaiTp;
use App\Models\RiwayatKelas;
use App\Models\Rombel;
use App\Models\SiswaRombel;
use Illuminate\Support\Facades\DB;

/** Rombel = pengajaran (mapel × semester) beserta guru pengampu dan pesertanya. */
class RombelService
{
    public function __construct(private AuditService $audit) {}

    public function buat(array $data, array $idGuru = []): Rombel
    {
        return DB::transaction(function () use ($data, $idGuru) {
            $rombel = Rombel::create($data);
            $rombel->guru()->sync($idGuru);
            $this->audit->catat('buat_rombel', 'rombel', $rombel->id_rombel, null, $data + ['id_guru' => $idGuru]);

            return $rombel->load(['mapel', 'guru']);
        });
    }

    public function ubah(Rombel $rombel, array $data): Rombel
    {
        return DB::transaction(function () use ($rombel, $data) {
            $sebelum = $rombel->only(array_keys($data));
            $rombel->update($data);
            $this->audit->catat('ubah_rombel', 'rombel', $rombel->id_rombel, $sebelum, $data);

            return $rombel->fresh(['mapel', 'guru']);
        });
    }

    public function setGuru(Rombel $rombel, array $idGuru): Rombel
    {
        return DB::transaction(function () use ($rombel, $idGuru) {
            $sebelum = $rombel->guru()->pluck('guru.id_guru')->all();
            $rombel->guru()->sync($idGuru);
            $this->audit->catat('set_guru_rombel', 'rombel', $rombel->id_rombel, ['id_guru' => $sebelum], ['id_guru' => $idGuru]);

            return $rombel->fresh(['mapel', 'guru']);
        });
    }

    /**
     * Peserta wajib dari semester yang sama. Mapel agama hanya untuk siswa dengan agama sama.
     *
     * @return array{ditambahkan:int, gagal: array<int,string>}
     */
    public function tambahPeserta(Rombel $rombel, array $idRiwayat = [], array $idKelas = []): array
    {
        $mapel = $rombel->mapel;

        $anggota = RiwayatKelas::with('siswa:id_siswa,nama,id_agama,status')
            ->where(function ($q) use ($idRiwayat, $idKelas, $rombel) {
                $q->whereIn('id_riwayat_kelas', $idRiwayat ?: [0]);
                if ($idKelas !== []) {
                    // Seluruh anggota kelas pada semester rombel.
                    $q->orWhere(fn ($w) => $w->whereIn('id_kelas', $idKelas)->where('id_semester', $rombel->id_semester));
                }
            })
            ->get();

        return DB::transaction(function () use ($rombel, $mapel, $anggota) {
            Rombel::whereKey($rombel->id_rombel)->lockForUpdate()->first();
            $sudah = SiswaRombel::where('id_rombel', $rombel->id_rombel)->pluck('id_riwayat_kelas')->flip();

            $gagal = [];
            $baru = 0;
            foreach ($anggota as $a) {
                $alasan = match (true) {
                    (int) $a->id_semester !== (int) $rombel->id_semester => 'Anggota kelas berasal dari semester lain.',
                    $a->siswa->status !== 'aktif' => 'Siswa tidak aktif.',
                    $mapel->isAgama() && (int) $a->siswa->id_agama !== (int) $mapel->id_agama => 'Agama siswa tidak sesuai dengan mapel agama ini.',
                    $sudah->has($a->id_riwayat_kelas) => null, // sudah peserta: lewati tanpa error
                    default => false,
                };
                if ($alasan === false) {
                    SiswaRombel::create(['id_rombel' => $rombel->id_rombel, 'id_riwayat_kelas' => $a->id_riwayat_kelas]);
                    $baru++;
                } elseif ($alasan !== null) {
                    $gagal[$a->id_riwayat_kelas] = $alasan;
                }
            }

            $this->audit->catat('tambah_peserta_rombel', 'rombel', $rombel->id_rombel, null, ['ditambahkan' => $baru, 'gagal' => array_keys($gagal)]);

            return ['ditambahkan' => $baru, 'gagal' => $gagal];
        });
    }

    public function hapusPeserta(Rombel $rombel, SiswaRombel $peserta): void
    {
        if ((int) $peserta->id_rombel !== (int) $rombel->id_rombel) {
            throw new AturanBisnisException('Peserta bukan anggota rombel ini.', 'NOT_FOUND', 404);
        }
        if (NilaiTp::where('id_siswa_rombel', $peserta->id_siswa_rombel)->exists()) {
            throw AturanBisnisException::terkunci('Peserta sudah memiliki nilai dan tidak dapat dikeluarkan.');
        }

        DB::transaction(function () use ($peserta) {
            $peserta->delete();
            $this->audit->catat('hapus_peserta_rombel', 'siswa_rombel', $peserta->id_siswa_rombel, $peserta->only(['id_rombel', 'id_riwayat_kelas']));
        });
    }

    public function hapus(Rombel $rombel): void
    {
        if ($rombel->tp()->exists() || $rombel->peserta()->exists()) {
            throw AturanBisnisException::terkunci('Rombel yang sudah memiliki TP atau peserta tidak dapat dihapus.');
        }

        DB::transaction(function () use ($rombel) {
            $rombel->guru()->detach();
            $rombel->delete();
            $this->audit->catat('hapus_rombel', 'rombel', $rombel->id_rombel, $rombel->only(['id_semester', 'id_mapel', 'nama']));
        });
    }
}
