<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\RiwayatKelas;
use App\Models\Semester;
use App\Models\Siswa;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class KelasController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request): JsonResponse
    {
        $semester = $this->semesterDari($request);

        $kelas = Kelas::where('id_tahun_ajaran', $semester->id_tahun_ajaran)
            ->withCount(['riwayat as jumlah_siswa' => fn ($q) => $q->where('id_semester', $semester->id_semester)])
            ->with(['waliKelas' => fn ($q) => $q->where('id_semester', $semester->id_semester)->with('guru:id_guru,nama')])
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        return response()->json($kelas);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:50', Rule::unique('kelas', 'nama')->where('id_tahun_ajaran', $request->integer('id_tahun_ajaran'))],
            'tingkat' => ['required', Rule::in(['X', 'XI', 'XII'])],
            'fase' => ['nullable', Rule::in(['E', 'F'])],
            'id_tahun_ajaran' => ['required', 'exists:tahun_ajaran,id_tahun_ajaran'],
        ]);

        $data['fase'] ??= $data['tingkat'] === 'X' ? 'E' : 'F';

        $kelas = Kelas::create($data);

        $this->audit->catat('buat_kelas', 'kelas', $kelas->id_kelas, null, null, $kelas->only(['nama', 'tingkat', 'fase']));

        return response()->json($kelas, 201);
    }

    public function update(Request $request, Kelas $kelas): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['sometimes', 'string', 'max:50', Rule::unique('kelas', 'nama')->where('id_tahun_ajaran', $kelas->id_tahun_ajaran)->ignore($kelas->id_kelas, 'id_kelas')],
            'tingkat' => ['sometimes', Rule::in(['X', 'XI', 'XII'])],
            'fase' => ['sometimes', Rule::in(['E', 'F'])],
        ]);

        $sebelum = $kelas->only(array_keys($data));
        $kelas->update($data);

        $this->audit->catat('ubah_kelas', 'kelas', $kelas->id_kelas, null, $sebelum, $kelas->only(array_keys($data)));

        return response()->json($kelas);
    }

    public function anggota(Request $request, Kelas $kelas): JsonResponse
    {
        $semester = $this->semesterDari($request);

        return response()->json(
            RiwayatKelas::with('siswa:id_siswa,nis,nisn,nama')
                ->where('id_kelas', $kelas->id_kelas)
                ->where('id_semester', $semester->id_semester)
                ->get()
        );
    }

    /** Menambahkan siswa ke kelas asal pada semester tertentu. Siswa yang sudah punya kelas dilewati. */
    public function tambahSiswa(Request $request, Kelas $kelas): JsonResponse
    {
        $data = $request->validate([
            'id_semester' => ['required', 'exists:semester,id_semester'],
            'siswa_ids' => ['required', 'array', 'min:1'],
            'siswa_ids.*' => ['integer', 'exists:siswa,id_siswa'],
        ]);

        $semester = Semester::findOrFail($data['id_semester']);
        $this->pastikanSatuTahunAjaran($kelas, $semester);

        $hasil = DB::transaction(function () use ($kelas, $semester, $data) {
            $ditambahkan = 0;
            $dilewati = [];

            foreach ($data['siswa_ids'] as $idSiswa) {
                $sudah = RiwayatKelas::where('id_siswa', $idSiswa)->where('id_semester', $semester->id_semester)->first();

                if ($sudah) {
                    $dilewati[] = ['id_siswa' => $idSiswa, 'id_kelas_sekarang' => $sudah->id_kelas];
                    continue;
                }

                RiwayatKelas::create([
                    'id_siswa' => $idSiswa,
                    'id_kelas' => $kelas->id_kelas,
                    'id_semester' => $semester->id_semester,
                ]);
                $ditambahkan++;
            }

            $this->audit->catat('tambah_anggota_kelas', 'kelas', $kelas->id_kelas, null, null, [
                'id_semester' => $semester->id_semester,
                'jumlah' => $ditambahkan,
            ]);

            return ['ditambahkan' => $ditambahkan, 'dilewati' => $dilewati];
        });

        return response()->json($hasil);
    }

    /** Memindahkan kelas asal siswa pada satu semester. Alasan opsional, tercatat di log. */
    public function pindahSiswa(Request $request, Siswa $siswa): JsonResponse
    {
        $data = $request->validate([
            'id_semester' => ['required', 'exists:semester,id_semester'],
            'id_kelas' => ['required', 'exists:kelas,id_kelas'],
            'alasan' => ['nullable', 'string', 'max:500'],
        ]);

        $semester = Semester::findOrFail($data['id_semester']);
        $kelas = Kelas::findOrFail($data['id_kelas']);
        $this->pastikanSatuTahunAjaran($kelas, $semester);

        DB::transaction(function () use ($siswa, $semester, $kelas, $data) {
            $riwayat = RiwayatKelas::where('id_siswa', $siswa->id_siswa)
                ->where('id_semester', $semester->id_semester)
                ->first();

            $sebelum = null;

            if ($riwayat) {
                $sebelum = ['id_kelas' => $riwayat->id_kelas];
                $riwayat->update(['id_kelas' => $kelas->id_kelas]);
            } else {
                RiwayatKelas::create([
                    'id_siswa' => $siswa->id_siswa,
                    'id_kelas' => $kelas->id_kelas,
                    'id_semester' => $semester->id_semester,
                ]);
            }

            // Perpindahan kelas asal dapat mengubah paket nilai; sinkronisasi dilakukan saat periode dibuka.
            $this->audit->catat('pindah_kelas', 'siswa', $siswa->id_siswa, $data['alasan'] ?? null, $sebelum, [
                'id_kelas' => $kelas->id_kelas,
                'id_semester' => $semester->id_semester,
            ]);
        });

        return response()->json(['message' => 'Siswa dipindahkan ke kelas ' . $kelas->nama . '.']);
    }
}
