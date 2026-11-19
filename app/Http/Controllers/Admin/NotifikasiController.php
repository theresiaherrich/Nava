<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class NotifikasiController extends Controller
{
    public function index()
    {
        // Database Nava saat ini belum memiliki tabel riwayat pengiriman WhatsApp.
        $failed = null;

        // Ini hanya menunjukkan konfigurasi service, bukan health-check jaringan.
        $waConfigured = (bool) config('wa.token') && (bool) config('wa.url');

        return view('admin.notifications.index', compact('failed', 'waConfigured'));
    }
}
