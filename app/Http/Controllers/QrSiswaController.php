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
        $gambarQr = QrCode::size(300)->generate($qrSiswa->kode_qr);

        // Hapus deklarasi XML agar SVG dapat dibaca browser.
        $gambarQr = preg_replace('/<\?xml.*?\?>/s', '', $gambarQr);
        $gambarQr = trim($gambarQr);

        return response($gambarQr, 200)
            ->header('Content-Type', 'image/svg+xml');
    }

    public function showWeb(Request $request): Response
    {
        $user = $request->user();

        // Pastikan hanya siswa yang dapat mengakses QR.
        if (!$user || $user->role !== 'SISWA') {
            abort(403, 'QR Code hanya dapat diakses oleh siswa.');
        }

        // Ambil data siswa yang sedang login.
        $siswa = $user->siswa;

        if (!$siswa) {
            abort(404, 'Data siswa tidak ditemukan.');
        }

        // Ambil QR milik siswa tersebut.
        $qrSiswa = QrSiswa::where('id_siswa', $siswa->id_siswa)->first();

        if (!$qrSiswa) {
            abort(404, 'QR Code siswa tidak ditemukan.');
        }

        // Hasilkan gambar QR dalam format SVG.
        // Hasilkan gambar QR dalam format SVG.
        $gambarQr = QrCode::size(300)->generate($qrSiswa->kode_qr);

        // Ambil isi SVG mulai dari tag <svg>.
        $gambarQr = (string) $gambarQr;
        $posisiSvg = strpos($gambarQr, '<svg');

        if ($posisiSvg === false) {
            abort(500, 'Format gambar QR tidak valid.');
        }

        $gambarQr = substr($gambarQr, $posisiSvg);

        return response($gambarQr, 200)
            ->header('Content-Type', 'image/svg+xml');
    }
}
