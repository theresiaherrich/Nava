<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class DashboardController extends Controller {
    public function index(Request $request) {
        return match ($request->user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'guru' => redirect()->route('guru.dashboard'),
            'tendik' => redirect()->route('tendik.dashboard'),
            'siswa' => redirect()->route('siswa.dashboard'),
            default => abort(403),
        };
    }
}