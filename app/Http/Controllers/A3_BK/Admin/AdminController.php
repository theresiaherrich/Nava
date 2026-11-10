<?php

namespace App\Http\Controllers\A3_BK\Admin;

use App\Http\Controllers\Controller;
use App\Models\DimensiRiasec;
use App\Models\Mapel;
use App\Models\PeriodeTes;
use App\Models\Profesi;
use App\Models\ProfilMapel;
use App\Models\ProgramStudi;
use App\Models\Semester;
use App\Models\SoalRiasec;
use Illuminate\Http\Request;

/**
 * AdminController — semua halaman area Administrator A3.
 */
class AdminController extends Controller
{
    // ============================================================
    // DASHBOARD
    // ============================================================

    public function dashboard()
    {
        $semester = Semester::where('aktif', true)->with('tahunAjaran')->first();

        $stats = [
            'total_soal' => SoalRiasec::count(),
            'soal_aktif' => SoalRiasec::where('aktif', true)->count(),
            'total_prodi' => ProgramStudi::count(),
            'total_profesi' => Profesi::count(),
            'total_mapel' => Mapel::whereHas('profilMapel')->count(),
            'periode_aktif' => PeriodeTes::where('status', 'aktif')->count(),
        ];

        $periodeSemesterIni = $semester
            ? PeriodeTes::where('id_semester', $semester->id_semester)->get()
            : collect();

        return view('bk.admin.dashboard', compact('stats', 'periodeSemesterIni', 'semester'));
    }

    // ============================================================
    // DIMENSI RIASEC — hanya edit deskripsi
    // ============================================================

    public function dimensiIndex()
    {
        $dimensi = DimensiRiasec::all();

        return view('bk.admin.dimensi.index', compact('dimensi'));
    }

    public function dimensiEdit(int $id)
    {
        $dimensi = DimensiRiasec::findOrFail($id);

        return view('bk.admin.dimensi.edit', compact('dimensi'));
    }

    public function dimensiUpdate(Request $request, int $id)
    {
        $request->validate(['deskripsi' => 'required|string|min:10']);
        DimensiRiasec::findOrFail($id)->update(['deskripsi' => $request->deskripsi]);

        return redirect()->route('admin.dimensi')->with('success', 'Deskripsi dimensi berhasil diperbarui.');
    }

    // ============================================================
    // PERTANYAAN TES — CRUD soal_riasec
    // ============================================================

    public function soalIndex()
    {
        $soal = SoalRiasec::with('dimensi')->orderBy('id_soal_riasec')->get();
        $dimensiList = DimensiRiasec::all();

        return view('bk.admin.soal.index', compact('soal', 'dimensiList'));
    }

    public function soalCreate()
    {
        $dimensiList = DimensiRiasec::all();

        return view('bk.admin.soal.create', compact('dimensiList'));
    }

    public function soalStore(Request $request)
    {
        $request->validate([
            'id_dimensi' => 'required|exists:dimensi_riasec,id_dimensi',
            'teks' => 'required|string|min:10',
            'aktif' => 'boolean',
        ]);

        SoalRiasec::create([
            'id_dimensi' => $request->id_dimensi,
            'teks' => $request->teks,
            'aktif' => $request->boolean('aktif', true),
        ]);

        return redirect()->route('admin.soal')->with('success', 'Soal berhasil ditambahkan.');
    }

    public function soalEdit(int $id)
    {
        $soal = SoalRiasec::with('dimensi')->findOrFail($id);
        $dimensiList = DimensiRiasec::all();

        return view('bk.admin.soal.edit', compact('soal', 'dimensiList'));
    }

    public function soalUpdate(Request $request, int $id)
    {
        $request->validate([
            'id_dimensi' => 'required|exists:dimensi_riasec,id_dimensi',
            'teks' => 'required|string|min:10',
            'aktif' => 'boolean',
        ]);

        SoalRiasec::findOrFail($id)->update([
            'id_dimensi' => $request->id_dimensi,
            'teks' => $request->teks,
            'aktif' => $request->boolean('aktif', false),
        ]);

        return redirect()->route('admin.soal')->with('success', 'Soal berhasil diperbarui.');
    }

    public function soalDestroy(int $id)
    {
        SoalRiasec::findOrFail($id)->delete();

        return redirect()->route('admin.soal')->with('success', 'Soal berhasil dihapus.');
    }

    public function soalToggle(int $id)
    {
        $soal = SoalRiasec::findOrFail($id);
        $soal->update(['aktif' => ! $soal->aktif]);
        $status = $soal->aktif ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.soal')->with('success', "Soal berhasil {$status}.");
    }

    // ============================================================
    // PERIODE TES — CRUD periode_tes
    // ============================================================

    public function periodeIndex()
    {
        $periode = PeriodeTes::with('semester.tahunAjaran')
            ->orderByDesc('id_periode_tes')
            ->get();
        $semesterList = Semester::with('tahunAjaran')->get();

        return view('bk.admin.periode.index', compact('periode', 'semesterList'));
    }

    public function periodeCreate()
    {
        $semesterList = Semester::with('tahunAjaran')->orderByDesc('id_semester')->get();

        return view('bk.admin.periode.create', compact('semesterList'));
    }

    public function periodeStore(Request $request)
    {
        $request->validate([
            'id_semester' => 'required|exists:semester,id_semester',
            'nama' => 'required|string|max:120',
            'tingkat' => 'required|in:X,XI,XII',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:terjadwal,aktif,selesai',
        ]);

        PeriodeTes::create($request->only(
            'id_semester', 'nama', 'tingkat', 'tanggal_mulai', 'tanggal_selesai', 'status'
        ));

        return redirect()->route('admin.periode')->with('success', 'Periode tes berhasil ditambahkan.');
    }

    public function periodeEdit(int $id)
    {
        $periode = PeriodeTes::findOrFail($id);
        $semesterList = Semester::with('tahunAjaran')->orderByDesc('id_semester')->get();

        return view('bk.admin.periode.edit', compact('periode', 'semesterList'));
    }

    public function periodeUpdate(Request $request, int $id)
    {
        $request->validate([
            'id_semester' => 'required|exists:semester,id_semester',
            'nama' => 'required|string|max:120',
            'tingkat' => 'required|in:X,XI,XII',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:terjadwal,aktif,selesai',
        ]);

        PeriodeTes::findOrFail($id)->update($request->only(
            'id_semester', 'nama', 'tingkat', 'tanggal_mulai', 'tanggal_selesai', 'status'
        ));

        return redirect()->route('admin.periode')->with('success', 'Periode tes berhasil diperbarui.');
    }

    public function periodeDestroy(int $id)
    {
        PeriodeTes::findOrFail($id)->delete();

        return redirect()->route('admin.periode')->with('success', 'Periode tes berhasil dihapus.');
    }

    // ============================================================
    // MAPEL PILIHAN — CRUD mapel + profil_mapel
    // ============================================================

    public function mapelIndex()
    {
        $mapel = Mapel::with('profilMapel', 'dimensi')->get();

        return view('bk.admin.mapel.index', compact('mapel'));
    }

    public function mapelCreate()
    {
        $dimensiList = DimensiRiasec::all();

        return view('bk.admin.mapel.create', compact('dimensiList'));
    }

    public function mapelStore(Request $request)
    {
        $request->validate([
            'id_dimensi' => 'required|exists:dimensi_riasec,id_dimensi',
            'kode' => 'required|string|max:30|unique:mapel,kode',
            'nama' => 'required|string|max:120',
            'rumpun' => 'required|string|max:50',
            'kode_holland' => ['required', 'string', 'size:3', 'regex:/^[RIASEC]{3}$/', function ($attr, $value, $fail) {
                if (count(array_unique(str_split($value))) !== 3) {
                    $fail('Profil RIASEC harus 3 huruf berbeda dari R,I,A,S,E,C.');
                }
            }],
            'deskripsi' => 'nullable|string',
        ]);

        $mapel = Mapel::create([
            'id_dimensi' => $request->id_dimensi,
            'kode' => strtoupper($request->kode),
            'nama' => $request->nama,
        ]);

        ProfilMapel::create([
            'id_mapel' => $mapel->id_mapel,
            'rumpun' => $request->rumpun,
            'deskripsi' => $request->deskripsi,
            'kode_holland' => strtoupper($request->kode_holland),
        ]);

        return redirect()->route('admin.mapel')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function mapelEdit(int $id)
    {
        $mapel = Mapel::with('profilMapel', 'dimensi')->findOrFail($id);
        $dimensiList = DimensiRiasec::all();

        return view('bk.admin.mapel.edit', compact('mapel', 'dimensiList'));
    }

    public function mapelUpdate(Request $request, int $id)
    {
        $mapel = Mapel::findOrFail($id);

        $request->validate([
            'id_dimensi' => 'required|exists:dimensi_riasec,id_dimensi',
            'kode' => 'required|string|max:30|unique:mapel,kode,'.$id.',id_mapel',
            'nama' => 'required|string|max:120',
            'rumpun' => 'required|string|max:50',
            'kode_holland' => ['required', 'string', 'size:3', 'regex:/^[RIASEC]{3}$/', function ($attr, $value, $fail) {
                if (count(array_unique(str_split($value))) !== 3) {
                    $fail('Profil RIASEC harus 3 huruf berbeda dari R,I,A,S,E,C.');
                }
            }],
            'deskripsi' => 'nullable|string',
        ]);

        $mapel->update([
            'id_dimensi' => $request->id_dimensi,
            'kode' => strtoupper($request->kode),
            'nama' => $request->nama,
        ]);

        $mapel->profilMapel()->updateOrCreate(
            ['id_mapel' => $id],
            [
                'rumpun' => $request->rumpun,
                'deskripsi' => $request->deskripsi,
                'kode_holland' => strtoupper($request->kode_holland),
            ]
        );

        return redirect()->route('admin.mapel')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function mapelDestroy(int $id)
    {
        Mapel::findOrFail($id)->delete();

        return redirect()->route('admin.mapel')->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    // ============================================================
    // PROGRAM STUDI — CRUD program_studi
    // ============================================================

    public function prodiIndex()
    {
        $prodi = ProgramStudi::withCount('profesi')->orderBy('nama')->get();

        return view('bk.admin.prodi.index', compact('prodi'));
    }

    public function prodiCreate()
    {
        $profesiList = Profesi::orderBy('nama')->get();

        return view('bk.admin.prodi.create', compact('profesiList'));
    }

    public function prodiStore(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:120|unique:program_studi,nama',
            'kategori' => 'nullable|string|max:60',
            'deskripsi' => 'required|string',
            'kode_holland' => ['required', 'string', 'size:3', 'regex:/^[RIASEC]{3}$/', function ($attr, $value, $fail) {
                if (count(array_unique(str_split($value))) !== 3) {
                    $fail('Profil RIASEC harus 3 huruf berbeda dari R,I,A,S,E,C.');
                }
            }],
            'profesi' => 'nullable|array',
            'profesi.*' => 'exists:profesi,id_profesi',
        ]);

        $prodi = ProgramStudi::create([
            'nama' => $request->nama,
            'kategori' => $request->kategori,
            'deskripsi' => $request->deskripsi,
            'kode_holland' => strtoupper($request->kode_holland),
        ]);

        if ($request->filled('profesi')) {
            $prodi->profesi()->sync($request->profesi);
        }

        return redirect()->route('admin.prodi')->with('success', 'Program studi berhasil ditambahkan.');
    }

    public function prodiEdit(int $id)
    {
        $prodi = ProgramStudi::with('profesi')->findOrFail($id);
        $profesiList = Profesi::orderBy('nama')->get();

        return view('bk.admin.prodi.edit', compact('prodi', 'profesiList'));
    }

    public function prodiUpdate(Request $request, int $id)
    {
        $prodi = ProgramStudi::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:120|unique:program_studi,nama,'.$id.',id_prodi',
            'kategori' => 'nullable|string|max:60',
            'deskripsi' => 'required|string',
            'kode_holland' => ['required', 'string', 'size:3', 'regex:/^[RIASEC]{3}$/', function ($attr, $value, $fail) {
                if (count(array_unique(str_split($value))) !== 3) {
                    $fail('Profil RIASEC harus 3 huruf berbeda dari R,I,A,S,E,C.');
                }
            }],
            'profesi' => 'nullable|array',
            'profesi.*' => 'exists:profesi,id_profesi',
        ]);

        $prodi->update([
            'nama' => $request->nama,
            'kategori' => $request->kategori,
            'deskripsi' => $request->deskripsi,
            'kode_holland' => strtoupper($request->kode_holland),
        ]);

        $prodi->profesi()->sync($request->profesi ?? []);

        return redirect()->route('admin.prodi')->with('success', 'Program studi berhasil diperbarui.');
    }

    public function prodiDestroy(int $id)
    {
        ProgramStudi::findOrFail($id)->delete();

        return redirect()->route('admin.prodi')->with('success', 'Program studi berhasil dihapus.');
    }

    // ============================================================
    // PROFESI — CRUD profesi
    // ============================================================

    public function profesiIndex()
    {
        $profesi = Profesi::with('programStudi')->orderBy('nama')->get();

        return view('bk.admin.profesi.index', compact('profesi'));
    }

    public function profesiCreate()
    {
        $prodiList = ProgramStudi::orderBy('nama')->get();

        return view('bk.admin.profesi.create', compact('prodiList'));
    }

    public function profesiStore(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:120|unique:profesi,nama',
            'deskripsi' => 'required|string',
            'prodi' => 'nullable|array',
            'prodi.*' => 'exists:program_studi,id_prodi',
        ]);

        $profesi = Profesi::create([
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
        ]);

        if ($request->filled('prodi')) {
            $profesi->programStudi()->sync($request->prodi);
        }

        return redirect()->route('admin.profesi')->with('success', 'Profesi berhasil ditambahkan.');
    }

    public function profesiEdit(int $id)
    {
        $profesi = Profesi::with('programStudi')->findOrFail($id);
        $prodiList = ProgramStudi::orderBy('nama')->get();

        return view('bk.admin.profesi.edit', compact('profesi', 'prodiList'));
    }

    public function profesiUpdate(Request $request, int $id)
    {
        $profesi = Profesi::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:120|unique:profesi,nama,'.$id.',id_profesi',
            'deskripsi' => 'required|string',
            'prodi' => 'nullable|array',
            'prodi.*' => 'exists:program_studi,id_prodi',
        ]);

        $profesi->update(['nama' => $request->nama, 'deskripsi' => $request->deskripsi]);
        $profesi->programStudi()->sync($request->prodi ?? []);

        return redirect()->route('admin.profesi')->with('success', 'Profesi berhasil diperbarui.');
    }

    public function profesiDestroy(int $id)
    {
        Profesi::findOrFail($id)->delete();

        return redirect()->route('admin.profesi')->with('success', 'Profesi berhasil dihapus.');
    }

    // ============================================================
    // VALIDASI PROFIL (BK mengubah status_validasi)
    // ============================================================

    public function validasiIndex()
    {
        $mapelMenunggu = ProfilMapel::with('mapel')
            ->where('status_validasi', 'menunggu')
            ->get();

        $prodiMenunggu = ProgramStudi::where('status_validasi', 'menunggu')->get();

        return view('bk.admin.validasi.index', compact('mapelMenunggu', 'prodiMenunggu'));
    }

    public function validasiMapel(Request $request, int $id)
    {
        $request->validate([
            'status_validasi' => 'required|in:tervalidasi,perlu_revisi',
            'catatan_validasi' => 'nullable|string',
        ]);

        $profil = ProfilMapel::findOrFail($id);
        $profil->update([
            'status_validasi' => $request->status_validasi,
            'catatan_validasi' => $request->catatan_validasi,
            'divalidasi_oleh' => Auth::id(),
            'waktu_validasi' => now(),
        ]);

        return redirect()->route('admin.validasi')->with('success', 'Status validasi mapel diperbarui.');
    }

    public function validasiProdi(Request $request, int $id)
    {
        $request->validate([
            'status_validasi' => 'required|in:tervalidasi,perlu_revisi',
            'catatan_validasi' => 'nullable|string',
        ]);

        $prodi = ProgramStudi::findOrFail($id);
        $prodi->update([
            'status_validasi' => $request->status_validasi,
            'catatan_validasi' => $request->catatan_validasi,
            'divalidasi_oleh' => Auth::id(),
            'waktu_validasi' => now(),
        ]);

        return redirect()->route('admin.validasi')->with('success', 'Status validasi program studi diperbarui.');
    }
}
