<?php

namespace App\Services;

use App\Exceptions\AturanBisnisException;
use App\Models\Kelas;
use App\Models\MutasiKelas;
use App\Models\NilaiTp;
use App\Models\PaketNilai;
use App\Models\RiwayatKelas;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\SiswaRombel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** Penempatan siswa ke kelas per semester (anggota_kelas) dan pemindahannya. */
class AnggotaKelasService
{
    public function __construct(private AuditService $audit) {}

    /** @param list<int> $idSiswa @return array{ditambahkan:int, gagal: array<int,string>} */
    public function tambah(Kelas $kelas, Semester $semester, array $idSiswa): array
    {
        $this->pastikanSemesterKelas($kelas, $semester);

        return DB::transaction(function () use ($kelas, $semester, $idSiswa) {
            $siswa = Siswa::whereKey($idSiswa)->lockForUpdate()->get()->keyBy('id_siswa');
            $sudah = RiwayatKelas::with('kelas:id_kelas,nama')->where('id_semester', $semester->id_semester)
                ->whereIn('id_siswa', $idSiswa)->get()->keyBy('id_siswa');

            $gagal = [];
            $baru = 0;
            foreach ($idSiswa as $id) {
                $s = $siswa->get($id);
                if (! $s || $s->status !== 'aktif') {
                    $gagal[$id] = 'Siswa tidak aktif.';
                } elseif ($r = $sudah->get($id)) {
                    $gagal[$id] = "Sudah menjadi anggota kelas {$r->kelas->nama} pada semester ini.";
                } else {
                    RiwayatKelas::create(['id_siswa' => $id, 'id_kelas' => $kelas->id_kelas, 'id_semester' => $semester->id_semester]);
                    $baru++;
                }
            }

            $this->audit->catat('tambah_anggota_kelas', 'kelas', $kelas->id_kelas, null, [
                'id_semester' => $semester->id_semester, 'ditambahkan' => $baru, 'gagal' => array_keys($gagal),
            ]);

            return ['ditambahkan' => $baru, 'gagal' => $gagal];
        });
    }

    /** Pindah kelas di semester yang sama; nilai ikut karena terikat ke anggota (riwayat_kelas). */
    public function pindah(RiwayatKelas $anggota, Kelas $tujuan, string $alasan): RiwayatKelas
    {
        $asal = $anggota->kelas;

        if ((int) $asal->id_kelas === (int) $tujuan->id_kelas) {
            throw new AturanBisnisException('Kelas tujuan sama dengan kelas asal.');
        }
        if ((int) $asal->id_tahun_ajaran !== (int) $tujuan->id_tahun_ajaran || $asal->tingkat !== $tujuan->tingkat) {
            throw new AturanBisnisException('Pindah kelas hanya ke kelas dengan tahun ajaran dan tingkat yang sama.');
        }

        // Nilai kelas asal yang sudah dikirim/dikunci tidak boleh berpindah paket diam-diam.
        $rombel = SiswaRombel::where('id_riwayat_kelas', $anggota->id_riwayat_kelas)->pluck('id_rombel');
        $terkirim = PaketNilai::whereIn('id_rombel', $rombel)->where('id_kelas', $asal->id_kelas)
            ->whereNotIn('status', PaketNilai::BISA_DIEDIT)->exists();
        if ($terkirim) {
            throw AturanBisnisException::terkunci('Siswa memiliki nilai pada paket kelas asal yang sudah dikirim/dikunci. Minta Kurikulum membuka koreksi dahulu.');
        }

        return DB::transaction(function () use ($anggota, $asal, $tujuan, $alasan) {
            $anggota->update(['id_kelas' => $tujuan->id_kelas]);
            MutasiKelas::create([
                'id_riwayat_kelas' => $anggota->id_riwayat_kelas,
                'dari_kelas' => $asal->id_kelas, 'ke_kelas' => $tujuan->id_kelas,
                'alasan' => $alasan, 'dipindah_oleh' => Auth::id(),
            ]);
            $this->audit->catat('pindah_kelas', 'riwayat_kelas', $anggota->id_riwayat_kelas,
                ['id_kelas' => $asal->id_kelas], ['id_kelas' => $tujuan->id_kelas], $alasan);

            return $anggota->fresh(['kelas', 'siswa']);
        });
    }

    public function keluarkan(RiwayatKelas $anggota): void
    {
        $adaNilai = NilaiTp::whereIn('id_siswa_rombel', $anggota->siswaRombel()->pluck('id_siswa_rombel'))->exists();
        if ($adaNilai) {
            throw AturanBisnisException::terkunci('Siswa sudah memiliki nilai di kelas ini. Gunakan pindah kelas, bukan hapus.');
        }

        DB::transaction(function () use ($anggota) {
            $anggota->siswaRombel()->delete();
            $anggota->delete();
            $this->audit->catat('keluarkan_anggota_kelas', 'riwayat_kelas', $anggota->id_riwayat_kelas, $anggota->only(['id_siswa', 'id_kelas', 'id_semester']));
        });
    }

    public function pastikanSemesterKelas(Kelas $kelas, Semester $semester): void
    {
        if ((int) $kelas->id_tahun_ajaran !== (int) $semester->id_tahun_ajaran) {
            throw new AturanBisnisException('Kelas dan semester berasal dari tahun ajaran berbeda.', 'BUSINESS_RULE', 422, ['id_semester' => ['Tidak sesuai tahun ajaran kelas.']]);
        }
    }
}
