<?php

namespace Database\Seeders;

use App\Models\Agama;
use App\Models\Ekskul;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\NilaiTp;
use App\Models\PeriodeNilai;
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
        $semester = Semester::aktif();
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

        $anggota = RiwayatKelas::with('siswa', 'kelas')->where('id_semester', $semester->id_semester)->get();
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
        $rombel('Bahasa Indonesia X', $mapel['BIN'], $sari, $anggota->whereIn('id_kelas', $kelasX));
        $rombel('PAI X', $mapel['PAI'], $budi, $anggota->where('siswa.id_agama', $islam));
        $rombel('PAK X', $mapel['PAK'], $sari, $anggota->where('siswa.id_agama', $kristen));

        foreach (['Bilangan berpangkat dan bentuk akar', 'Persamaan dan fungsi kuadrat', 'Barisan dan deret', 'Trigonometri dasar'] as $i => $desk) {
            Tp::firstOrCreate(['id_rombel' => $mtk->id_rombel, 'urutan' => $i + 1], ['deskripsi' => $desk]);
        }

        $periode->pastikanAda($semester);
        $sts = PeriodeNilai::where('id_semester', $semester->id_semester)->where('jenis', 'STS')->first();
        if ($sts->status === 'belum_diatur') {
            $sts->update(['tgl_buka' => now()->subDays(7), 'tgl_tutup' => now()->addDays(14), 'status' => 'dibuka']);
        }

        // Nilai contoh TP 1–2 (draf).
        $idUserBudi = User::where('username', 'budi')->value('id_user');
        $tp = $mtk->tp()->get();
        foreach (SiswaRombel::where('id_rombel', $mtk->id_rombel)->get() as $i => $p) {
            foreach ($tp->take(2) as $j => $t) {
                NilaiTp::firstOrCreate(['id_tp' => $t->id_tp, 'id_siswa_rombel' => $p->id_siswa_rombel],
                    ['id_periode' => $sts->id_periode, 'nilai' => [82, 74, 90][$i % 3] - $j * 4, 'diubah_oleh' => $idUserBudi]);
            }
        }

        foreach (['Pramuka', 'Paskibra', 'Basket'] as $e) {
            Ekskul::firstOrCreate(['nama' => $e]);
        }
        TemplateRapor::firstOrCreate(['jenis' => 'STS', 'nama' => 'Rapor STS 2026'], ['view' => 'rapor.sts', 'judul' => 'LAPORAN HASIL BELAJAR TENGAH SEMESTER', 'tempat_cetak' => 'Malang', 'aktif' => true]);
        TemplateRapor::firstOrCreate(['jenis' => 'SAS', 'nama' => 'Rapor Semester 2026'], ['view' => 'rapor.semester', 'judul' => 'LAPORAN HASIL BELAJAR', 'tempat_cetak' => 'Malang', 'aktif' => true]);
    }
}
