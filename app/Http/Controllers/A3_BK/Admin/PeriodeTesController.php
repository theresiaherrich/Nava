<?php

namespace App\Http\Controllers\A3_BK\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeriodeTes;
use App\Models\Semester;
use Illuminate\Http\Request;

/**
 * PeriodeTesController — CRUD periode tes RIASEC.
 */
class PeriodeTesController extends Controller
{
    public function index()
    {
        $periode = PeriodeTes::with('semester.tahunAjaran')
            ->orderByDesc('id_periode_tes')
            ->get();
        $semesterList = Semester::with('tahunAjaran')->get();

        return view('bk.admin.periode.index', compact('periode', 'semesterList'));
    }

    public function create()
    {
        $semesterList = Semester::with('tahunAjaran')->orderByDesc('id_semester')->get();

        return view('bk.admin.periode.create', compact('semesterList'));
    }

    public function store(Request $request)
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

    public function edit(int $id)
    {
        $periode = PeriodeTes::findOrFail($id);
        $semesterList = Semester::with('tahunAjaran')->orderByDesc('id_semester')->get();

        return view('bk.admin.periode.edit', compact('periode', 'semesterList'));
    }

    public function update(Request $request, int $id)
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

    public function destroy(int $id)
    {
        PeriodeTes::findOrFail($id)->delete();

        return redirect()->route('admin.periode')->with('success', 'Periode tes berhasil dihapus.');
    }
}
