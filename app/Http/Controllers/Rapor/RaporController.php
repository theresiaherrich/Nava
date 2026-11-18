<?php

namespace App\Http\Controllers\Rapor;

use App\Exceptions\AturanBisnisException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rapor\CetakRaporRequest;
use App\Models\Kelas;
use App\Models\LogAktivitas;
use App\Models\PeriodeNilai;
use App\Models\Rapor;
use App\Models\RiwayatKelas;
use App\Services\RaporAccess;
use App\Services\RaporService;
use App\Support\AcademicAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/** Cetak, arsip & versi, terbit/cabut rapor (wali kelas: kelasnya; Kurikulum: semua). */
class RaporController extends Controller
{
    public function __construct(private RaporService $rapor) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'id_kelas' => ['required', 'integer', 'exists:kelas,id_kelas'],
            'id_periode' => ['required', 'integer', 'exists:periode_nilai,id_periode'],
        ]);
        $kelas = Kelas::findOrFail($request->integer('id_kelas'));
        $periode = PeriodeNilai::findOrFail($request->integer('id_periode'));
        RaporAccess::pastikanKelolaKelas($kelas, $periode->id_semester);

        $anggota = RiwayatKelas::with('siswa:id_siswa,nis,nama')->where('id_kelas', $kelas->id_kelas)->where('id_semester', $periode->id_semester)->get();
        $rapor = Rapor::where('id_periode', $periode->id_periode)->whereIn('id_riwayat_kelas', $anggota->pluck('id_riwayat_kelas'))
            ->orderByDesc('versi')->get()->groupBy('id_riwayat_kelas');

        return $this->ok([
            'status_nilai' => $this->rapor->statusKelas($kelas, $periode),
            'siswa' => $anggota->sortBy(fn ($a) => mb_strtolower($a->siswa->nama))->values()->map(fn ($a) => [
                'id_riwayat_kelas' => $a->id_riwayat_kelas,
                'siswa' => $a->siswa,
                'versi' => $rapor->get($a->id_riwayat_kelas, collect())->map(fn (Rapor $r) => $r->only(['id_rapor', 'versi', 'status', 'created_at', 'waktu_terbit']))->values(),
            ]),
        ]);
    }

    public function cetakSiswa(CetakRaporRequest $request): JsonResponse
    {
        $anggota = RiwayatKelas::with('kelas')->findOrFail($request->integer('id_riwayat_kelas'));
        $periode = PeriodeNilai::findOrFail($request->integer('id_periode'));
        RaporAccess::pastikanKelolaKelas($anggota->kelas, $periode->id_semester);
        if ((int) $anggota->id_semester !== (int) $periode->id_semester) {
            throw new AturanBisnisException('Periode dan anggota kelas berbeda semester.');
        }

        $template = $this->rapor->pastikanBisaCetak($anggota->kelas, $periode);
        [$r, $baru] = $this->rapor->cetak($anggota, $periode, $template);

        return $this->ok($r->only(['id_rapor', 'versi', 'status']) + ['versi_baru' => $baru], $baru ? "Rapor versi {$r->versi} dibuat." : 'Data tidak berubah; memakai versi terakhir.');
    }

    public function cetakKelas(CetakRaporRequest $request): Response
    {
        $kelas = Kelas::findOrFail($request->integer('id_kelas'));
        $periode = PeriodeNilai::findOrFail($request->integer('id_periode'));
        RaporAccess::pastikanKelolaKelas($kelas, $periode->id_semester);

        $file = $this->rapor->cetakKelas($kelas, $periode, $request->input('format', 'pdf'));

        return response($file['isi'], 200, [
            'Content-Type' => $file['mime'],
            'Content-Disposition' => 'attachment; filename="'.$file['nama'].'"',
        ]);
    }

    public function unduh(Request $request, Rapor $rapor): Response
    {
        $rapor->load('riwayatKelas.kelas');
        RaporAccess::pastikanKelolaKelas($rapor->riwayatKelas->kelas, $rapor->riwayatKelas->id_semester);

        return $this->pdf($rapor, $request->boolean('pratinjau'));
    }

    public function riwayatCetak(Request $request): JsonResponse
    {
        $request->validate(['id_kelas' => ['required', 'integer', 'exists:kelas,id_kelas'], 'id_semester' => ['nullable', 'integer']]);
        $kelas = Kelas::findOrFail($request->integer('id_kelas'));
        $semester = $request->integer('id_semester') ?: AcademicAccess::semesterAktif()->id_semester;
        RaporAccess::pastikanKelolaKelas($kelas, $semester);

        $page = LogAktivitas::with('user:id_user,nama')->whereIn('aksi', ['cetak_rapor_kelas', 'terbitkan_rapor'])
            ->where('nama_entitas', 'kelas')->where('entitas_id', $kelas->id_kelas)->orderByDesc('id_log')->paginate($this->perPage());

        return $this->paginated($page);
    }

    public function terbitkan(Request $request): JsonResponse
    {
        $request->validate([
            'id_kelas' => ['required', 'integer', 'exists:kelas,id_kelas'],
            'id_periode' => ['required', 'integer', 'exists:periode_nilai,id_periode'],
        ]);
        $n = $this->rapor->terbitkan(Kelas::findOrFail($request->integer('id_kelas')), PeriodeNilai::findOrFail($request->integer('id_periode')));

        return $this->ok(['jumlah_terbit' => $n], "{$n} rapor diterbitkan.");
    }

    public function cabut(Request $request, Rapor $rapor): JsonResponse
    {
        $request->validate(['alasan' => ['required', 'string', 'min:5', 'max:500']]);

        return $this->ok($this->rapor->cabut($rapor, $request->string('alasan'))->only(['id_rapor', 'versi', 'status']), 'Rapor dicabut.');
    }

    public function pdf(Rapor $rapor, bool $pratinjau = false): Response
    {
        $nama = 'rapor-'.Str::slug($rapor->data_final['siswa']['nis']).'-'.strtolower($rapor->data_final['jenis']).'-v'.$rapor->versi.'.pdf';

        return response($this->rapor->isiFile($rapor), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($pratinjau ? 'inline' : 'attachment').'; filename="'.$nama.'"',
        ]);
    }
}
