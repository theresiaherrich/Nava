<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\QrSiswa;
use App\Models\LokasiSekolah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PresensiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'id_siswa' => ['nullable', 'integer', 'exists:siswa,id_siswa'],
            'status' => ['nullable', Rule::in(['Hadir', 'Alpa', 'Sakit', 'Izin', 'Terlambat'])],
            'tanggal_mulai' => ['nullable', 'date_format:Y-m-d'],
            'tanggal_akhir' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:tanggal_mulai'],
        ]);

        $query = Presensi::with(['siswa', 'izin', 'sakit']);

        if (!empty($validated['id_siswa'])) {
            $query->where('id_siswa', $validated['id_siswa']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['tanggal'])) {
            $query->whereDate('tanggal', $validated['tanggal']);
        }

        if (!empty($validated['tanggal_mulai'])) {
            $query->whereDate('tanggal', '>=', $validated['tanggal_mulai']);
        }

        if (!empty($validated['tanggal_akhir'])) {
            $query->whereDate('tanggal', '<=', $validated['tanggal_akhir']);
        }

        $presensi = $query
            ->orderByDesc('tanggal')
            ->orderByDesc('waktu_presensi')
            ->get();

        return response()->json([
            'message' => 'Data presensi berhasil diambil.',
            'data' => $presensi,
        ]);
    }

    public function riwayatSiswa(Request $request, int $id_siswa): JsonResponse
    {
        $user = $request->user();

        // Siswa hanya boleh melihat riwayat miliknya sendiri.
        if ($user->role === 'SISWA') {
            $siswaLogin = $user->siswa;

            if (!$siswaLogin || (int) $siswaLogin->id_siswa !== $id_siswa) {
                return response()->json([
                    'message' => 'Kamu hanya dapat melihat riwayat presensimu sendiri.',
                ], 403);
            }
        } elseif (!in_array($user->role, ['ADMIN', 'BUDAYA_POSITIF'], true)) {
            return response()->json([
                'message' => 'Kamu tidak memiliki akses untuk melihat riwayat presensi ini.',
            ], 403);
        }

        $siswa = Siswa::find($id_siswa);

        if (!$siswa) {
            return response()->json([
                'message' => 'Data siswa tidak ditemukan.',
            ], 404);
        }

        $riwayat = $siswa->presensi()
            ->orderByDesc('tanggal')
            ->orderByDesc('waktu_presensi')
            ->get();

        return response()->json([
            'message' => 'Riwayat presensi siswa berhasil diambil.',
            'data' => [
                'siswa' => $siswa->nama,
                'riwayat' => $riwayat,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Admin dan Budaya Positif hanya boleh menggunakan QR.
        // Siswa hanya boleh menggunakan Geofencing.
        if (
            in_array($user->role, ['ADMIN', 'BUDAYA_POSITIF'], true)
            && $request->input('metode') !== 'QR'
        ) {
            return response()->json([
                'message' => 'Admin presensi hanya dapat mencatat presensi melalui QR Code.',
            ], 403);
        }

        if (
            $user->role === 'SISWA'
            && $request->input('metode') !== 'Geofencing'
        ) {
            return response()->json([
                'message' => 'Siswa hanya dapat melakukan presensi melalui Geofencing.',
            ], 403);
        }

        if (!in_array($user->role, ['ADMIN', 'BUDAYA_POSITIF', 'SISWA'], true)) {
            return response()->json([
                'message' => 'Kamu tidak memiliki akses untuk mencatat presensi.',
            ], 403);
        }

        // Mengambil identitas siswa dari akun yang sedang login.
        $idSiswaLogin = null;

        if ($user->role === 'SISWA') {
            $siswaLogin = $user->siswa;

            if (!$siswaLogin) {
                return response()->json([
                    'message' => 'Akun ini belum terhubung dengan data siswa.',
                ], 422);
            }

            $idSiswaLogin = $siswaLogin->id_siswa;
        }

        $validated = $request->validate([
            'metode' => ['required', Rule::in(['QR', 'Geofencing'])],

            'kode_qr' => [
                'required_if:metode,QR',
                'nullable',
                'string',
                'exists:qr_siswa,kode_qr',
            ],

            'latitude' => [
                'required_if:metode,Geofencing',
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'required_if:metode,Geofencing',
                'nullable',
                'numeric',
                'between:-180,180',
            ],
        ]);

        $waktuSekarang = now('Asia/Jakarta');
        $tanggal = $waktuSekarang->toDateString();
        $jam = $waktuSekarang->format('H:i');

        // Presensi hanya dibuka mulai pukul 06.00.
        if ($jam < '06:00') {
            return response()->json([
                'message' => 'Presensi belum dibuka. Presensi dimulai pukul 06.00.',
            ], 422);
        }

        // Menentukan status berdasarkan waktu server.
        $status = $jam <= '07:00' ? 'Hadir' : 'Terlambat';

        // Validasi lokasi khusus untuk metode Geofencing.
        if ($validated['metode'] === 'Geofencing') {
            $lokasiSekolah = LokasiSekolah::first();

            if (!$lokasiSekolah) {
                return response()->json([
                    'message' => 'Lokasi sekolah belum dikonfigurasi.',
                ], 422);
            }

            $jarak = $this->hitungJarak(
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                (float) $lokasiSekolah->latitude,
                (float) $lokasiSekolah->longitude
            );

            if ($jarak > $lokasiSekolah->radius) {
                return response()->json([
                    'message' => 'Kamu berada di luar area presensi.',
                    'jarak_meter' => round($jarak, 2),
                    'radius_meter' => $lokasiSekolah->radius,
                ], 422);
            }
        }

        // Menentukan siswa berdasarkan metode presensi.
        if ($validated['metode'] === 'QR') {
            $idSiswa = QrSiswa::where('kode_qr', $validated['kode_qr'])
                ->value('id_siswa');
        } else {
            // Tidak mengambil id_siswa dari request frontend.
            $idSiswa = $idSiswaLogin;
        }

        $hasil = DB::transaction(function () use (
            $idSiswa,
            $validated,
            $waktuSekarang,
            $tanggal,
            $jam,
            $status
        ) {
            // Mengunci data siswa selama proses pencatatan
            // untuk mencegah presensi ganda secara bersamaan.
            $siswa = Siswa::where('id_siswa', $idSiswa)
                ->lockForUpdate()
                ->first();

            if (!$siswa) {
                return [
                    'error' => true,
                    'message' => 'Data siswa tidak ditemukan.',
                    'status_code' => 404,
                ];
            }

            // Periksa apakah siswa sudah melakukan presensi aktual.
            // Catatan Alpa tidak termasuk presensi aktual.
            $sudahPresensiAktual = Presensi::where('id_siswa', $siswa->id_siswa)
                ->whereDate('tanggal', $tanggal)
                ->whereIn('status', ['Hadir', 'Terlambat'])
                ->exists();

            if ($sudahPresensiAktual) {
                return [
                    'error' => true,
                    'message' => 'Kamu sudah melakukan presensi hari ini.',
                    'status_code' => 409,
                ];
            }

            // Jika sudah pukul 07.31 dan belum ada Alpa,
            // buat catatan Alpa terlebih dahulu.
            if ($jam >= '07:31') {
                $sudahAlpa = Presensi::where('id_siswa', $siswa->id_siswa)
                    ->whereDate('tanggal', $tanggal)
                    ->where('status', 'Alpa')
                    ->exists();

                if (!$sudahAlpa) {
                    Presensi::create([
                        'id_siswa' => $siswa->id_siswa,
                        'metode' => null,
                        'status' => 'Alpa',
                        'hari' => $waktuSekarang->locale('id')->translatedFormat('l'),
                        'tanggal' => $tanggal,
                        'waktu_presensi' => null,
                    ]);
                }
            }

            // Simpan kejadian presensi aktual.
            // Jika sebelumnya sudah ada Alpa, catatan ini tetap dibuat
            // sebagai kejadian Terlambat yang terpisah.
            $presensi = Presensi::create([
                'id_siswa' => $siswa->id_siswa,
                'metode' => $validated['metode'],
                'status' => $status,
                'hari' => $waktuSekarang->locale('id')->translatedFormat('l'),
                'tanggal' => $tanggal,
                'waktu_presensi' => $waktuSekarang->format('H:i:s'),
            ]);

            return [
                'error' => false,
                'data' => $presensi,
            ];
        });

        if ($hasil['error'] ?? false) {
            return response()->json([
                'message' => $hasil['message'],
            ], $hasil['status_code']);
        }

        return response()->json([
            'message' => 'Presensi berhasil dicatat.',
            'data' => $hasil['data'],
        ], 201);
    }

    private function hitungJarak(
        float $latitudePengguna,
        float $longitudePengguna,
        float $latitudeSekolah,
        float $longitudeSekolah
    ): float {
        $radiusBumi = 6371000;

        $lat1 = deg2rad($latitudePengguna);
        $lat2 = deg2rad($latitudeSekolah);

        $deltaLat = deg2rad($latitudeSekolah - $latitudePengguna);
        $deltaLong = deg2rad($longitudeSekolah - $longitudePengguna);

        $a = sin($deltaLat / 2) ** 2
            + cos($lat1) * cos($lat2)
            * sin($deltaLong / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $radiusBumi * $c;
    }
}