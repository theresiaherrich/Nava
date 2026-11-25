<?php

namespace Database\Seeders;

use App\Models\Agama;
use App\Models\Ekskul;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\NilaiTp;
use App\Models\PesertaEkskul;
use App\Models\RiwayatKelas;
use App\Models\Rombel;
use App\Models\Semester;
use App\Models\SiswaRombel;
use App\Models\TemplateRapor;
use App\Models\Tp;
use App\Models\User;
use App\Services\PeriodeService;
use Illuminate\Database\Seeder;

/** Contoh penilaian: mapel, rombel, TP, periode STS yang sedang dibuka, dan beberapa nilai. */
class DevPenilaianSeeder extends Seeder
{
    public function run(PeriodeService $periode): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $semester = Semester::where('nama', 'GANJIL')->whereHas('tahunAjaran', fn ($q) => $q->where('nama', '2026/2027'))->firstOrFail();
        $islam = Agama::where('nama', 'Islam')->value('id_agama');
        $kristen = Agama::where('nama', 'Kristen')->value('id_agama');
        $budi = Guru::where('nama', 'Budi Santoso')->first();
        $sari = Guru::where('nama', 'Sari Wulandari')->first();

        $mapel = [];
        foreach ([
            ['PAI', 'Pendidikan Agama Islam dan Budi Pekerti', 1, $islam],
            ['PAK', 'Pendidikan Agama Kristen dan Budi Pekerti', 1, $kristen],
            ['BIN', 'Bahasa Indonesia', 3, null],
            ['MTK', 'Matematika', 4, null],
        ] as [$kode, $nama, $urut, $agama]) {
            $mapel[$kode] = Mapel::firstOrCreate(['kode' => $kode], ['nama' => $nama, 'urutan_rapor' => $urut, 'id_agama' => $agama, 'kelompok' => 'Umum']);
        }

        $anggota = RiwayatKelas::with('siswa', 'kelas')->where('id_semester', $semester->id_semester)
            ->whereHas('siswa.user', fn ($q) => $q->whereIn('username', ['andi', 'bunga', 'citra', ...array_map(fn ($i) => sprintf('siswa_a2_%02d', $i), range(4, 12))]))
            ->get();
        $kelasX = Kelas::where('tingkat', 'X')->pluck('id_kelas');

        $rombel = fn (string $nama, Mapel $m, Guru $g, $peserta) => tap(
            Rombel::firstOrCreate(['id_semester' => $semester->id_semester, 'id_mapel' => $m->id_mapel, 'nama' => $nama]),
            function (Rombel $r) use ($g, $peserta) {
                $r->guru()->syncWithoutDetaching([$g->id_guru]);
                foreach ($peserta as $a) {
                    SiswaRombel::firstOrCreate(['id_rombel' => $r->id_rombel, 'id_riwayat_kelas' => $a->id_riwayat_kelas]);
                }
            },
        );

        // Rombel campuran: Matematika X berisi siswa X-A dan X-B.
        $mtk = $rombel('Matematika X', $mapel['MTK'], $budi, $anggota->whereIn('id_kelas', $kelasX));
        $bin = $rombel('Bahasa Indonesia X', $mapel['BIN'], $sari, $anggota->whereIn('id_kelas', $kelasX));
        $pai = $rombel('PAI X', $mapel['PAI'], $budi, $anggota->where('siswa.id_agama', $islam));
        $pak = $rombel('PAK X', $mapel['PAK'], $sari, $anggota->where('siswa.id_agama', $kristen));

        foreach (['Bilangan berpangkat dan bentuk akar', 'Persamaan dan fungsi kuadrat', 'Barisan dan deret', 'Trigonometri dasar'] as $i => $desk) {
            Tp::firstOrCreate(['id_rombel' => $mtk->id_rombel, 'urutan' => $i + 1], ['deskripsi' => $desk]);
        }
        foreach ([
            [$bin, ['Mengidentifikasi struktur teks deskripsi', 'Menulis tanggapan berdasarkan bukti']],
            [$pai, ['Mengenali nilai kejujuran dalam kehidupan sehari-hari', 'Menerapkan sikap saling menghargai']],
            [$pak, ['Menjelaskan nilai kasih dalam hubungan sosial', 'Merancang praktik kepedulian di sekolah']],
        ] as [$r, $tujuan]) {
            foreach ($tujuan as $i => $desk) {
                Tp::firstOrCreate(['id_rombel' => $r->id_rombel, 'urutan' => $i + 1], ['deskripsi' => $desk]);
            }
        }

        $periodeList = $periode->pastikanAda($semester);
        $sts = $periodeList->firstWhere('jenis', 'STS');
        $sas = $periodeList->firstWhere('jenis', 'SAS');
        if ($sts->status === 'belum_diatur') {
            $sts->update(['tgl_buka' => now()->subDays(7), 'tgl_tutup' => now()->addDays(14), 'status' => 'dibuka']);
        }

        $idBudi = User::where('username', 'budi')->value('id_user');
        $idSari = User::where('username', 'sari')->value('id_user');
        foreach ([[$mtk, 2, 4, $idBudi], [$bin, 1, 2, $idSari], [$pai, 1, 2, $idBudi], [$pak, 1, 2, $idSari]] as [$r, $batasSts, $jumlahTp, $pengampu]) {
            $tp = $r->tp()->where('urutan', '<=', $jumlahTp)->get();
            $peserta = SiswaRombel::with('riwayatKelas.siswa')->where('id_rombel', $r->id_rombel)
                ->whereIn('id_riwayat_kelas', $anggota->pluck('id_riwayat_kelas'))->orderBy('id_siswa_rombel')->get();
            foreach ($peserta as $i => $p) {
                foreach ($tp as $t) {
                    if ($r->is($mtk) && $p->riwayatKelas->siswa->nis === '1003' && $t->urutan === 2) {
                        continue;
                    }
                    NilaiTp::firstOrCreate(['id_tp' => $t->id_tp, 'id_siswa_rombel' => $p->id_siswa_rombel], [
                        'id_periode' => $t->urutan <= $batasSts ? $sts->id_periode : $sas->id_periode,
                        'nilai' => [82, 74, 90][$i % 3] + [0, -4, 4, 8][$t->urutan - 1],
                        'diubah_oleh' => $pengampu,
                    ]);
                }
            }
        }

        foreach (['Pramuka', 'Paskibra', 'Basket'] as $e) {
            Ekskul::firstOrCreate(['nama' => $e]);
        }
        $pramuka = Ekskul::where('nama', 'Pramuka')->firstOrFail();
        foreach ($anggota->sortBy('siswa.nis')->values() as $i => $a) {
            PesertaEkskul::firstOrCreate(['id_ekskul' => $pramuka->id_ekskul, 'id_riwayat_kelas' => $a->id_riwayat_kelas],
                ['predikat' => ['SB', 'B', 'K'][$i % 3]]);
        }
        TemplateRapor::firstOrCreate(['jenis' => 'STS', 'nama' => 'Rapor STS 2026'], ['view' => 'rapor.sts', 'judul' => 'LAPORAN HASIL BELAJAR TENGAH SEMESTER', 'tempat_cetak' => 'Malang', 'aktif' => true]);
        TemplateRapor::firstOrCreate(['jenis' => 'SAS', 'nama' => 'Rapor Semester 2026'], ['view' => 'rapor.semester', 'judul' => 'LAPORAN HASIL BELAJAR', 'tempat_cetak' => 'Malang', 'aktif' => true]);
    }
}
