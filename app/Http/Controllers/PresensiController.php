<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\RiwayatPerubahanPresensi;
use App\Models\Siswa;
use App\Models\QrSiswa;
use App\Models\LokasiSekolah;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class PresensiController extends Controller
{

    public function kelola()
    {
        $role = strtolower(request('role', 'wali_kelas'));

        $siswa = [
            [
                'nama' => 'Mutiara Bintang',
                'nisn' => '245150600',
                'kelas' => 'XI MIPA 2',
                'status' => 'Telat',
                'jam' => '07:28',
                'metode' => 'QR Code',
                'berkas' => '-',
                'keterangan' => 'Terlambat masuk',
            ],
        ];

        return view('presensi.kelola.index', compact('role', 'siswa'));
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'id_siswa' => ['nullable', 'integer', 'exists:siswa,id_siswa'],
            'status' => ['nullable', Rule::in([
                'Hadir', 'Alpa', 'Sakit', 'Izin', 'Terlambat'
            ])],
            'tanggal_mulai' => ['nullable', 'date_format:Y-m-d'],
            'tanggal_akhir' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:tanggal_mulai',
            ],
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
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

        if (!empty($validated['search'])) {
            $search = $validated['search'];

            $query->whereHas('siswa', function ($siswaQuery) use ($search) {
                $siswaQuery->where('nama', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $presensi = $query
            ->orderByDesc('tanggal')
            ->orderByDesc('waktu_presensi')
            ->paginate($validated['per_page'] ?? 10);

        return response()->json([
            'message' => 'Data presensi berhasil diambil.',
            'data' => $presensi->items(),
            'meta' => [
                'current_page' => $presensi->currentPage(),
                'per_page' => $presensi->perPage(),
                'total' => $presensi->total(),
                'last_page' => $presensi->lastPage(),
            ],
        ]);
    }

    public function riwayatSiswa(Request $request, int $id_siswa): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'SISWA') {
            $siswaLogin = $user->siswa;

            if (!$siswaLogin || (int) $siswaLogin->id_siswa !== $id_siswa) {
                return response()->json([
                    'message' => 'Kamu hanya dapat melihat riwayat presensimu sendiri.',
                ], 403);
            }
        } elseif ($user->role === 'GURU') {
            return response()->json([
                'message' => 'Akses riwayat presensi guru menunggu integrasi data penugasan wali kelas.',
            ], 409);
        } elseif (!in_array($user->role, [
            'ADMIN',
            'BUDAYA_POSITIF',
            'KURIKULUM',
        ], true)) {
            return response()->json([
                'message' => 'Kamu tidak memiliki akses untuk melihat riwayat presensi ini.',
            ], 403);
        }

        $validated = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $siswa = Siswa::find($id_siswa);

        if (!$siswa) {
            return response()->json([
                'message' => 'Data siswa tidak ditemukan.',
            ], 404);
        }

        $query = $siswa->presensi();

        if (!empty($validated['tanggal'])) {
            $query->whereDate('tanggal', $validated['tanggal']);
        }

        $riwayat = $query
            ->with(['izin', 'sakit'])
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

        if (
            in_array($user->role, ['ADMIN', 'BUDAYA_POSITIF'], true)
            && $request->input('metode') !== 'QR'
        ) {
            return response()->json([
                'message' => 'Admin presensi hanya dapat mencatat presensi melalui QR Code.',
            ], 403);
        }

        if (!in_array($user->role, ['ADMIN', 'BUDAYA_POSITIF', 'SISWA'], true)) {
            return response()->json([
                'message' => 'Kamu tidak memiliki akses untuk mencatat presensi.',
            ], 403);
        }

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
            'accuracy' => [
                'required_if:metode,Geofencing',
                'nullable',
                'numeric',
                'min:0',
                'max:50',
            ],
        ]);

        $waktuSekarang = now('Asia/Jakarta');
        $tanggal = $waktuSekarang->toDateString();
        $jam = $waktuSekarang->format('H:i');

        if ($jam < '06:00') {
            return response()->json([
                'message' => 'Presensi belum dibuka. Presensi dimulai pukul 06.00.',
            ], 422);
        }

        if ($jam >= '07:30') {
            return response()->json([
                'message' => 'Batas waktu presensi telah berakhir. Siswa yang belum melakukan presensi akan tercatat Alpa secara otomatis.',
            ], 422);
        }

        $status = $jam <= '07:00' ? 'Hadir' : 'Terlambat';

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

        if ($validated['metode'] === 'QR') {
            $idSiswa = QrSiswa::where('kode_qr', $validated['kode_qr'])
                ->value('id_siswa');
        } else {
            $idSiswa = $idSiswaLogin;
        }

        if (
            $user->role === 'SISWA'
            && (int) $idSiswa !== (int) $idSiswaLogin
        ) {
            return response()->json([
                'message' => 'Kamu hanya dapat melakukan presensi menggunakan QR Code milikmu sendiri.',
            ], 403);
        }

        $hasil = DB::transaction(function () use (
            $idSiswa,
            $validated,
            $waktuSekarang,
            $tanggal,
            $status
        ) {
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

            $catatanHariIni = Presensi::where('id_siswa', $siswa->id_siswa)
                ->whereDate('tanggal', $tanggal)
                ->lockForUpdate()
                ->get();

            // Cegah siswa memiliki lebih dari satu catatan presensi per hari.
            if ($catatanHariIni->isNotEmpty()) {
                return [
                    'error' => true,
                    'message' => 'Kamu sudah memiliki catatan presensi hari ini.',
                    'status_code' => 409,
                ];
            }

            $presensi = Presensi::create([
                'id_siswa' => $siswa->id_siswa,
                'metode' => $validated['metode'],
                'status' => $status,
                'tanggal' => $tanggal,
                'waktu_presensi' => $waktuSekarang->format('H:i:s'),
            ]);

            return [
                'error' => false,
                'data' => $presensi,
                'message' => 'Presensi berhasil dicatat.',
            ];
        });

        if ($hasil['error'] ?? false) {
            return response()->json([
                'message' => $hasil['message'],
            ], $hasil['status_code']);
        }

        return response()->json([
            'message' => $hasil['message'],
            'data' => $hasil['data'],
        ], 201);
    }

    public function tambahPresensiManual(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, ['ADMIN', 'BUDAYA_POSITIF'], true)) {
            return response()->json([
                'message' => 'Hanya Admin dan Budaya Positif yang dapat menambahkan presensi manual.',
            ], 403);
        }

        $validated = $request->validate([
            'id_siswa' => ['required', 'integer', 'exists:siswa,id_siswa'],
            'status' => ['required', Rule::in(['Hadir', 'Izin', 'Sakit'])],
            'waktu_presensi' => ['required', 'date_format:H:i'],
            'file_bukti' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $waktuSekarang = now('Asia/Jakarta');
        $tanggal = $waktuSekarang->toDateString();
        $fileBukti = null;

        try {
            $hasil = DB::transaction(function () use (
                $validated,
                $user,
                $waktuSekarang,
                $tanggal,
                $request,
                &$fileBukti
            ) {
                $siswa = Siswa::where('id_siswa', $validated['id_siswa'])
                    ->lockForUpdate()
                    ->first();

                if (!$siswa) {
                    return [
                        'error' => true,
                        'message' => 'Data siswa tidak ditemukan.',
                        'status_code' => 404,
                    ];
                }

                // Cari catatan presensi siswa pada tanggal hari ini.
                $presensi = Presensi::where('id_siswa', $siswa->id_siswa)
                    ->whereDate('tanggal', $tanggal)
                    ->lockForUpdate()
                    ->first();

                // Hanya catatan Alpa yang boleh dikoreksi menjadi Izin atau Sakit.
                if ($presensi && !(
                    $presensi->status === 'Alpa'
                    && in_array($validated['status'], ['Izin', 'Sakit'], true)
                )) {
                    return [
                        'error' => true,
                        'message' => 'Siswa sudah memiliki catatan presensi hari ini.',
                        'status_code' => 409,
                    ];
                }

                // Simpan file bukti jika tersedia.
                if ($request->hasFile('file_bukti')) {
                    $fileBukti = $request->file('file_bukti')
                        ->store('presensi/bukti', 'public');
                }

                if ($presensi) {
                    // Catat perubahan status Alpa menjadi Izin atau Sakit.
                    RiwayatPerubahanPresensi::create([
                        'id_presensi' => $presensi->id_presensi,
                        'id_user' => $user->id_user,
                        'status_sebelumnya' => $presensi->status,
                        'status_baru' => $validated['status'],
                        'alasan_perubahan' => $validated['keterangan']
                            ?? 'Perubahan status oleh petugas presensi.',
                        'waktu_perubahan' => $waktuSekarang,
                    ]);

                    $presensi->update([
                        'metode' => 'Manual',
                        'status' => $validated['status'],
                        'waktu_presensi' => $validated['waktu_presensi'] . ':00',
                        'file_bukti' => $fileBukti,
                        'keterangan' => $validated['keterangan'] ?? null,
                    ]);

                    $pesan = 'Status presensi Alpa berhasil diperbarui menjadi ' .
                        $validated['status'] . '.';
                    $statusCode = 200;
                } else {
                    // Buat catatan baru jika siswa belum memiliki presensi.
                    $presensi = Presensi::create([
                        'id_siswa' => $siswa->id_siswa,
                        'metode' => 'Manual',
                        'status' => $validated['status'],
                        'tanggal' => $tanggal,
                        'waktu_presensi' => $validated['waktu_presensi'] . ':00',
                        'file_bukti' => $fileBukti,
                        'keterangan' => $validated['keterangan'] ?? null,
                    ]);

                    $pesan = 'Catatan presensi berhasil ditambahkan.';
                    $statusCode = 201;
                }

                // Simpan atau perbarui detail Izin.
                if ($validated['status'] === 'Izin') {
                    $presensi->izin()->updateOrCreate(
                        ['id_presensi' => $presensi->id_presensi],
                        [
                            'file_bukti' => $fileBukti,
                            'alasan' => $validated['keterangan'] ?? null,
                        ]
                    );
                }

                // Simpan atau perbarui detail Sakit.
                if ($validated['status'] === 'Sakit') {
                    $presensi->sakit()->updateOrCreate(
                        ['id_presensi' => $presensi->id_presensi],
                        [
                            'file_bukti' => $fileBukti,
                        ]
                    );
                }

                return [
                    'error' => false,
                    'data' => $presensi->load(['siswa', 'izin', 'sakit']),
                    'message' => $pesan,
                    'status_code' => $statusCode,
                ];
            });
        } catch (Throwable $e) {
            if ($fileBukti) {
                Storage::disk('public')->delete($fileBukti);
            }

            throw $e;
        }

        if ($hasil['error']) {
            return response()->json([
                'message' => $hasil['message'],
            ], $hasil['status_code']);
        }

        return response()->json([
            'message' => $hasil['message'],
            'data' => $hasil['data'],
        ], $hasil['status_code']);
    }

    public function update(Request $request, int $id_presensi): JsonResponse
    {
        $user = $request->user();

        if (!$user || $user->role !== 'ADMIN') {
            return response()->json([
                'message' => 'Hanya Admin yang dapat mengubah status presensi.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['Hadir', 'Alpa', 'Sakit', 'Izin', 'Terlambat']),
            ],
            'alasan_perubahan' => ['required', 'string'],
        ]);

        $hasil = DB::transaction(function () use (
            $id_presensi,
            $validated,
            $user
        ) {
            $presensi = Presensi::where('id_presensi', $id_presensi)
                ->lockForUpdate()
                ->first();

            if (!$presensi) {
                return null;
            }

            $statusSebelumnya = $presensi->status;
            $statusBaru = $validated['status'];

            if ($statusSebelumnya === $statusBaru) {
                return [
                    'berubah' => false,
                    'data' => $presensi->load(['siswa', 'izin', 'sakit']),
                ];
            }

            // Simpan riwayat perubahan status.
            RiwayatPerubahanPresensi::create([
                'id_presensi' => $presensi->id_presensi,
                'id_user' => $user->id_user,
                'status_sebelumnya' => $statusSebelumnya,
                'status_baru' => $statusBaru,
                'alasan_perubahan' => $validated['alasan_perubahan'],
                'waktu_perubahan' => now('Asia/Jakarta'),
            ]);

            $presensi->update([
                'status' => $statusBaru,
            ]);

            // Sinkronkan detail Izin jika status berubah menjadi Izin.
            if ($statusBaru === 'Izin') {
                $presensi->izin()->updateOrCreate(
                    ['id_presensi' => $presensi->id_presensi],
                    [
                        'alasan' => $validated['alasan_perubahan'],
                    ]
                );
            }

            // Sinkronkan detail Sakit jika status berubah menjadi Sakit.
            if ($statusBaru === 'Sakit') {
                $presensi->sakit()->updateOrCreate(
                    ['id_presensi' => $presensi->id_presensi],
                    []
                );
            }

            return [
                'berubah' => true,
                'data' => $presensi->fresh()->load([
                    'siswa',
                    'izin',
                    'sakit',
                ]),
            ];
        });

        if (!$hasil) {
            return response()->json([
                'message' => 'Data presensi tidak ditemukan.',
            ], 404);
        }

        if (!$hasil['berubah']) {
            return response()->json([
                'message' => 'Status presensi tidak berubah karena nilainya sama.',
                'data' => $hasil['data'],
            ]);
        }

        return response()->json([
            'message' => 'Status presensi berhasil diperbarui.',
            'data' => $hasil['data'],
        ]);
    }

    public function riwayatPerubahan(
        Request $request,
        int $id_presensi
    ): JsonResponse {
        $user = $request->user();

        $presensi = Presensi::with('siswa')->find($id_presensi);

        if (!$presensi) {
            return response()->json([
                'message' => 'Data presensi tidak ditemukan.',
            ], 404);
        }

        if ($user->role === 'SISWA') {
            $siswaLogin = $user->siswa;

            if (
                !$siswaLogin ||
                (int) $siswaLogin->id_siswa !== (int) $presensi->id_siswa
            ) {
                return response()->json([
                    'message' => 'Kamu hanya dapat melihat riwayat perubahan presensimu sendiri.',
                ], 403);
            }
        } elseif ($user->role === 'GURU') {
            return response()->json([
                'message' => 'Akses riwayat perubahan presensi guru menunggu integrasi data penugasan wali kelas.',
            ], 409);
        } elseif (!in_array($user->role, [
            'ADMIN',
            'BUDAYA_POSITIF',
            'KURIKULUM',
        ], true)) {
            return response()->json([
                'message' => 'Kamu tidak memiliki akses untuk melihat riwayat perubahan presensi ini.',
            ], 403);
        }

        $riwayat = RiwayatPerubahanPresensi::with('user')
            ->where('id_presensi', $presensi->id_presensi)
            ->orderByDesc('waktu_perubahan')
            ->get();

        return response()->json([
            'message' => 'Riwayat perubahan presensi berhasil diambil.',
            'data' => [
                'presensi' => $presensi,
                'riwayat_perubahan' => $riwayat,
            ],
        ]);
    }

    private function hitungJarak(
        float $latitude1,
        float $longitude1,
        float $latitude2,
        float $longitude2
    ): float {
        $radiusBumi = 6371000;

        $lat1 = deg2rad($latitude1);
        $lat2 = deg2rad($latitude2);

        $deltaLat = deg2rad($latitude2 - $latitude1);
        $deltaLon = deg2rad($longitude2 - $longitude1);

        $a = sin($deltaLat / 2) ** 2
            + cos($lat1) * cos($lat2)
            * sin($deltaLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $radiusBumi * $c;
    }

    public function statistik(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'tanggal_mulai' => ['nullable', 'date_format:Y-m-d'],
            'tanggal_akhir' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:tanggal_mulai',
            ],
            'bulan' => ['nullable', 'integer', 'between:1,12'],
            'tahun' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        $query = Presensi::query();

        if (!empty($validated['tanggal'])) {
            $query->whereDate('tanggal', $validated['tanggal']);

            $periode = [
                'tanggal' => $validated['tanggal'],
            ];
        } elseif (
            !empty($validated['tanggal_mulai']) ||
            !empty($validated['tanggal_akhir'])
        ) {
            if (!empty($validated['tanggal_mulai'])) {
                $query->whereDate(
                    'tanggal',
                    '>=',
                    $validated['tanggal_mulai']
                );
            }

            if (!empty($validated['tanggal_akhir'])) {
                $query->whereDate(
                    'tanggal',
                    '<=',
                    $validated['tanggal_akhir']
                );
            }

            $periode = [
                'tanggal_mulai' => $validated['tanggal_mulai'] ?? null,
                'tanggal_akhir' => $validated['tanggal_akhir'] ?? null,
            ];
        } elseif (!empty($validated['bulan'])) {
            $tahun = $validated['tahun'] ?? now('Asia/Jakarta')->year;

            $query->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $validated['bulan']);

            $periode = [
                'bulan' => $validated['bulan'],
                'tahun' => $tahun,
            ];
        } else {
            $tanggal = now('Asia/Jakarta')->toDateString();

            $query->whereDate('tanggal', $tanggal);

            $periode = [
                'tanggal' => $tanggal,
            ];
        }

        $jumlahSiswa = Siswa::count();

        $statistik = (clone $query)
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $totalCatatan = (clone $query)->count();

        return response()->json([
            'message' => 'Statistik presensi berhasil diambil.',
            'data' => [
                'periode' => $periode,
                'jumlah_siswa' => $jumlahSiswa,
                'total_catatan' => $totalCatatan,
                'hadir' => (int) ($statistik['Hadir'] ?? 0),
                'terlambat' => (int) ($statistik['Terlambat'] ?? 0),
                'alpa' => (int) ($statistik['Alpa'] ?? 0),
                'izin' => (int) ($statistik['Izin'] ?? 0),
                'sakit' => (int) ($statistik['Sakit'] ?? 0),
            ],
        ]);
    }

    public function rekap(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tanggal_mulai' => ['nullable', 'date_format:Y-m-d'],
            'tanggal_akhir' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:tanggal_mulai',
            ],
            'search' => ['nullable', 'string', 'max:100'],
            'id_kelas' => ['nullable', 'integer', 'exists:kelas,id_kelas'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Siswa::query()
            ->with([
                'riwayatKelas' => function ($riwayatQuery) {
                    $riwayatQuery
                        ->with('kelas')
                        ->orderByDesc('id_riwayat_kelas');
                },
            ]);

        if (!empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function ($siswaQuery) use ($search) {
                $siswaQuery->where('nama', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        if (!empty($validated['id_kelas'])) {
            $query->whereHas('riwayatKelas', function ($riwayatQuery) use ($validated) {
                $riwayatQuery->where('id_kelas', $validated['id_kelas']);
            });
        }

        $siswa = $query
            ->orderBy('nama')
            ->paginate($validated['per_page'] ?? 10);

        $data = $siswa->getCollection()->map(function ($item) use ($validated) {
            $presensiQuery = $item->presensi();

            if (!empty($validated['tanggal_mulai'])) {
                $presensiQuery->whereDate(
                    'tanggal',
                    '>=',
                    $validated['tanggal_mulai']
                );
            }

            if (!empty($validated['tanggal_akhir'])) {
                $presensiQuery->whereDate(
                    'tanggal',
                    '<=',
                    $validated['tanggal_akhir']
                );
            }

            $jumlah = (clone $presensiQuery)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $riwayatKelasTerbaru = $item->riwayatKelas->first();

            return [
                'id_siswa' => $item->id_siswa,
                'nama' => $item->nama,
                'nisn' => $item->nisn,
                'kelas' => $riwayatKelasTerbaru?->kelas?->nama,
                'hadir' => (int) ($jumlah['Hadir'] ?? 0),
                'izin' => (int) ($jumlah['Izin'] ?? 0),
                'alpa' => (int) ($jumlah['Alpa'] ?? 0),
                'persentase' => null,
            ];
        });

        return response()->json([
            'message' => 'Rekap presensi berhasil diambil.',
            'data' => $data,
            'meta' => [
                'current_page' => $siswa->currentPage(),
                'per_page' => $siswa->perPage(),
                'total' => $siswa->total(),
                'last_page' => $siswa->lastPage(),
            ],
        ]);
    }

    public function aturan(): JsonResponse
    {
        return response()->json([
            'message' => 'Aturan presensi berhasil diambil.',
            'data' => [
                'jam_mulai' => '06:00',
                'batas_hadir' => '07:00',
                'jam_terlambat' => '07:01',
                'jam_alpa' => '07:30',
            ],
        ]);
    }
}
