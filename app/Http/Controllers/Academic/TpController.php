<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TpController extends Controller
{
    public function index()
    {
        // Nanti bisa diambil dari DB berdasarkan rombel yang dipilih
        return view('academic.tp.index');
    }
}
