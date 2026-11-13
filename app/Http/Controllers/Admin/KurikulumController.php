<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KurikulumController extends Controller
{
    public function kktp() {
        return view('admin.kurikulum.kktp');
    }

    public function rekapNilai() {
        return view('admin.kurikulum.rekap-nilai');
    }

    public function formatRapor() {
        return view('admin.kurikulum.format-rapor');
    }

    public function cetakRapor() {
        return view('admin.kurikulum.cetak-rapor');
    }

    public function terbitkanRapor() {
        return view('admin.kurikulum.terbitkan-rapor');
    }

    public function arsipRapor() {
        return view('admin.kurikulum.arsip-rapor');
    }
}