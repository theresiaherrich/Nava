<?php

namespace App\Http\Controllers\A3_BK\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\A3_BK\Concerns\RecordsA3Activity;
use App\Models\DimensiRiasec;
use Illuminate\Http\Request;

/**
 * DimensiController — mengelola dimensi RIASEC (edit deskripsi).
 */
class DimensiController extends Controller
{
    use RecordsA3Activity;

    public function index()
    {
        $dimensi = DimensiRiasec::all();

        return view('bk.admin.dimensi.index', compact('dimensi'));
    }

    public function edit(int $id)
    {
        $dimensi = DimensiRiasec::findOrFail($id);

        return view('bk.admin.dimensi.edit', compact('dimensi'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate(['deskripsi' => 'required|string|min:10']);
        DimensiRiasec::findOrFail($id)->update(['deskripsi' => $request->deskripsi]);
        $this->recordA3Activity('diperbarui', 'dimensi_riasec', $id);

        return redirect()->route('admin.dimensi')->with('success', 'Deskripsi dimensi berhasil diperbarui.');
    }
}
