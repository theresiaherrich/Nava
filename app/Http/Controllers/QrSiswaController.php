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

        // Siswa hanya boleh melihat QR miliknya sendiri.
        if ($user->role === 'SISWA') {
            $siswaLogin = $user->siswa;

            if (!$siswaLogin || (int) $siswaLogin->id_siswa !== $id_siswa) {
                abort(403, 'Kamu hanya dapat melihat QR Code milikmu sendiri.');
            }
        } elseif (!in_array($user->role, ['ADMIN', 'BUDAYA_POSITIF'], true)) {
            abort(403, 'Kamu tidak memiliki akses untuk melihat QR Code ini.');
        }

        $qrSiswa = QrSiswa::where('id_siswa', $id_siswa)->first();

        if (!$qrSiswa) {
            abort(404, 'QR Code siswa tidak ditemukan.');
        }

        $gambarQr = QrCode::size(300)
            ->generate($qrSiswa->kode_qr);

        return response($gambarQr, 200)
            ->header('Content-Type', 'image/svg+xml');
    }
}