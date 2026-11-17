<?php

namespace App\Http\Controllers\Kurikulum;

use App\Http\Controllers\Controller;
use App\Support\Dummy\Kurikulum;

class KktpController extends Controller
{
    /** KKTP & Deskripsi Capaian (desain 5.03). */
    public function index()
    {
        return view('kurikulum.kktp', ['data' => Kurikulum::kktp()]);
    }
}
