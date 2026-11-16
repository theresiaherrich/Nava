<?php

namespace App\Http\Controllers;

use App\Support\Dummy\DashboardGuru;
use Illuminate\Http\Request;

class DashboardGuruController extends Controller
{
    /** Dashboard Guru Mapel. Varian desain: ?state=kosong (Guru 05). */
    public function index(Request $request)
    {
        return view('dashboard-guru.index', [
            'data' => DashboardGuru::data($request->query('state') === 'kosong'),
        ]);
    }
}
