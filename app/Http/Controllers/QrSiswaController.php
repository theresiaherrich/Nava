<?php

namespace App\Http\Controllers;

use App\Models\QrSiswa;
use F9WebLtd\QrCode\Facades\QrCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QrSiswaController extends Controller
{
    public function show(Request $request, int $id_siswa): Response
    {
        $user = $request->user();

        // QR Code hanya dapat diakses oleh siswa.
        if ($user->role !== 'SISWA') {
            abort(403, 'QR Code hanya dapat diakses oleh siswa.');
        }

        // Siswa hanya boleh melihat QR Code miliknya sendiri.
        $siswaLogin = $user->siswa;

        if (!$siswaLogin || (int) $siswaLogin->id_siswa !== $id_siswa) {
            abort(403, 'Kamu hanya dapat melihat QR Code milikmu sendiri.');
        }

        // Mengambil QR Code berdasarkan ID siswa.
        $qrSiswa = QrSiswa::where('id_siswa', $id_siswa)->first();

        if (!$qrSiswa) {
            abort(404, 'QR Code siswa tidak ditemukan.');
        }

        // Menghasilkan gambar QR Code dalam format SVG.
        $gambarQr = QrCode::size(300)
            ->generate($qrSiswa->kode_qr);

        return response($gambarQr, 200)
            ->header('Content-Type', 'image/svg+xml');
    }
}
