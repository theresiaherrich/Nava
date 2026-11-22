<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\RiwayatPerubahanPresensi;
use App\Models\Siswa;
use App\Models\QrSiswa;
use App\Models\LokasiSekolah;
use App\Models\Semester;
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
        $user = $request->user();

        $validated = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'id_siswa' => ['nullable', 'integer', 'exists:siswa,id_siswa'],
            'id_kelas' => ['nullable', 'integer', 'exists:kelas,id_kelas'],
            'status' => ['nullable', 'string', 'max:30'],
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

        $query = Presensi::with([
            'siswa',
            'siswa.riwayatKelas.kelas',
            'izin',
            'sakit',
        ]);

        // Pembatasan data untuk Guru Wali Kelas.
        if ($user->role === 'GURU') {
            $guru = $user->guru;

            if (!$guru) {
                return response()->json([
                    'message' => 'Akun guru belum terhubung dengan data guru.',
                ], 403);
            }

            $semesterAktif = Semester::where('aktif', true)
                ->whereHas('tahunAjaran', function ($query) {
                    $query->where('aktif', true);
                })
                ->first();

            if (!$semesterAktif) {
                return response()->json([
                    'message' => 'Semester dan tahun ajaran aktif belum dikonfigurasi.',
                ], 409);
            }

            $idKelasWali = \App\Models\WaliKelas::where('id_guru', $guru->id_guru)
                ->where('id_semester', $semesterAktif->id_semester)
                ->pluck('id_kelas');

            $query->whereHas('siswa.riwayatKelas', function ($query) use (
                $semesterAktif,
                $idKelasWali
            ) {
                $query->where('id_semester', $semesterAktif->id_semester)
                    ->whereIn('id_kelas', $idKelasWali);
            });
        }

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

            if (
                !$siswaLogin ||
                (int) $siswaLogin->id_siswa !== $id_siswa
            ) {
                return response()->json([
                    'message' => 'Kamu hanya dapat melihat riwayat presensimu sendiri.',
                ], 403);
            }
        } elseif ($user->role === 'GURU') {
            $guru = $user->guru;

            if (!$guru) {
                return response()->json([
                    'message' => 'Akun guru belum terhubung dengan data guru.',
                ], 403);
            }

            // Mengambil semester aktif pada tahun ajaran aktif.
            $semesterAktif = Semester::where('aktif', true)
                ->whereHas('tahunAjaran', function ($query) {
                    $query->where('aktif', true);
                })
                ->first();

            if (!$semesterAktif) {
                return response()->json([
                    'message' => 'Semester dan tahun ajaran aktif belum dikonfigurasi.',
                ], 409);
            }

            // Memastikan siswa berada di kelas yang diampu guru
            // pada semester dan tahun ajaran aktif.
            $memilikiAkses = \App\Models\WaliKelas::where('id_guru', $guru->id_guru)
                ->where('id_semester', $semesterAktif->id_semester)
                ->whereHas('kelas.riwayatKelas', function ($query) use (
                    $id_siswa,
                    $semesterAktif
                ) {
                    $query->where('id_siswa', $id_siswa)
                        ->where('id_semester', $semesterAktif->id_semester);
                })
                ->exists();

            if (!$memilikiAkses) {
                return response()->json([
                    'message' => 'Kamu hanya dapat melihat riwayat presensi siswa di kelas yang menjadi tanggung jawabmu.',
                ], 403);
            }
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

        if ($jam >= '17:30') {
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

            $semesterAktif = \App\Models\Semester::where('aktif', true)
                ->whereHas('tahunAjaran', function ($query) {
                    $query->where('aktif', true);
                })
                ->first();

            if (!$semesterAktif) {
                return [
                    'error' => true,
                    'message' => 'Semester dan tahun ajaran aktif belum dikonfigurasi.',
                    'status_code' => 409,
                ];
            }

            $kelasSiswa = \App\Models\RiwayatKelas::where(
                'id_siswa',
                $siswa->id_siswa
            )
                ->where('id_semester', $semesterAktif->id_semester)
                ->where('id_kelas', $validated['id_kelas'])
                ->exists();

            if (!$kelasSiswa) {
                return [
                    'error' => true,
                    'message' => 'Kelas yang dipilih tidak sesuai dengan kelas aktif siswa.',
                    'status_code' => 422,
                ];
            }

            $presensiTerakhir = Presensi::where('id_siswa', $siswa->id_siswa)
                ->whereDate('tanggal', $tanggal)
                ->orderByDesc('waktu_presensi')
                ->orderByDesc('id_presensi')
                ->lockForUpdate()
                ->first();

            if (
                $presensiTerakhir &&
                $presensiTerakhir->status === $validated['status']
            ) {
                return [
                    'error' => true,
                    'message' => 'Presensi dengan status tersebut sudah tercatat sebagai status terakhir siswa hari ini.',
                    'status_code' => 409,
                ];
            }

            $semesterAktif = \App\Models\Semester::where('aktif', true)
                ->whereHas('tahunAjaran', function ($query) {
                    $query->where('aktif', true);
                })
                ->first();

            if (!$semesterAktif) {
                return [
                    'error' => true,
                    'message' => 'Semester dan tahun ajaran aktif belum dikonfigurasi.',
                    'status_code' => 409,
                ];
            }

            $kelasSiswa = \App\Models\RiwayatKelas::where(
                'id_siswa',
                $siswa->id_siswa
            )
                ->where('id_semester', $semesterAktif->id_semester)
                ->where('id_kelas', $validated['id_kelas'])
                ->exists();

            if (!$kelasSiswa) {
                return [
                    'error' => true,
                    'message' => 'Kelas yang dipilih tidak sesuai dengan kelas aktif siswa.',
                    'status_code' => 422,
                ];
            }

            $presensiTerakhir = Presensi::where('id_siswa', $siswa->id_siswa)
                ->whereDate('tanggal', $tanggal)
                ->orderByDesc('waktu_presensi')
                ->orderByDesc('id_presensi')
                ->first();

            if (
                $presensiTerakhir &&
                $presensiTerakhir->status === $validated['status']
            ) {
                return [
                    'error' => true,
                    'message' => 'Presensi dengan status tersebut sudah tercatat sebagai status terakhir siswa hari ini.',
                    'status_code' => 422,
                ];
            }

            $presensiTerakhir = Presensi::where(
                'id_siswa',
                $siswa->id_siswa
            )
                ->whereDate('tanggal', $tanggal)
                ->orderByDesc('waktu_presensi')
                ->orderByDesc('id_presensi')
                ->lockForUpdate()
                ->first();

            if (
                $presensiTerakhir &&
                strtolower($presensiTerakhir->status) === strtolower($status)
            ) {
                return [
                    'error' => true,
                    'message' => 'Kamu sudah presensi dengan status ' . $status . ' hari ini.',
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
            'id_kelas' => ['required', 'integer', 'exists:kelas,id_kelas'],
            'status' => ['required', Rule::in(['Hadir', 'Izin', 'Sakit'])],
            'waktu_presensi' => ['required', 'date_format:H:i'],
            'file_bukti' => [
                Rule::requiredIf(fn () => in_array(
                    $request->input('status'),
                    ['Izin', 'Sakit'],
                    true
                )),
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
            ],
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

                $semesterAktif = \App\Models\Semester::where('aktif', true)
                    ->whereHas('tahunAjaran', function ($query) {
                        $query->where('aktif', true);
                    })
                    ->first();

                if (!$semesterAktif) {
                    return [
                        'error' => true,
                        'message' => 'Semester dan tahun ajaran aktif belum dikonfigurasi.',
                        'status_code' => 409,
                    ];
                }

                $kelasSiswa = \App\Models\RiwayatKelas::where('id_siswa', $siswa->id_siswa)
                    ->where('id_semester', $semesterAktif->id_semester)
                    ->where('id_kelas', $validated['id_kelas'])
                    ->exists();

                if (!$kelasSiswa) {
                    return [
                        'error' => true,
                        'message' => 'Kelas yang dipilih tidak sesuai dengan kelas aktif siswa.',
                        'status_code' => 422,
                    ];
                }

                $presensiTerakhir = Presensi::where('id_siswa', $siswa->id_siswa)
                    ->whereDate('tanggal', $tanggal)
                    ->orderByDesc('waktu_presensi')
                    ->orderByDesc('id_presensi')
                    ->lockForUpdate()
                    ->first();

                if (
                    $presensiTerakhir &&
                    $presensiTerakhir->status === $validated['status']
                ) {
                    return [
                        'error' => true,
                        'message' => 'Presensi dengan status tersebut sudah tercatat sebagai status terakhir siswa hari ini.',
                        'status_code' => 409,
                    ];
                }

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

                if ($validated['status'] === 'Izin') {
                    $presensi->izin()->firstOrCreate(
                        ['id_presensi' => $presensi->id_presensi],
                        [
                            'file_bukti' => $fileBukti,
                            'alasan' => $validated['keterangan'] ?? null,
                        ]
                    );
                }

                if ($validated['status'] === 'Sakit') {
                    $presensi->sakit()->firstOrCreate(
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

        if (!$user || !in_array($user->role, ['ADMIN', 'GURU'], true)) {
            return response()->json([
                'message' => 'Kamu tidak memiliki akses untuk mengoreksi data presensi.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                'Hadir',
                'Alpa',
                'Terlambat',
                'Izin',
                'Sakit',
            ])],
            'alasan_perubahan' => ['required', 'string'],
            'keterangan' => ['nullable', 'string'],
        ]);

        // Pembatasan edit untuk Guru Wali Kelas.
        if ($user->role === 'GURU') {
            $guru = $user->guru;

            if (!$guru) {
                return response()->json([
                    'message' => 'Akun guru belum terhubung dengan data guru.',
                ], 403);
            }

            $semesterAktif = Semester::where('aktif', true)
                ->whereHas('tahunAjaran', function ($query) {
                    $query->where('aktif', true);
                })
                ->first();

            if (!$semesterAktif) {
                return response()->json([
                    'message' => 'Semester dan tahun ajaran aktif belum dikonfigurasi.',
                ], 409);
            }

            $presensiTarget = Presensi::find($id_presensi);

            if (!$presensiTarget) {
                return response()->json([
                    'message' => 'Data presensi tidak ditemukan.',
                ], 404);
            }

            $memilikiAkses = \App\Models\WaliKelas::where(
                'id_guru',
                $guru->id_guru
            )
                ->where('id_semester', $semesterAktif->id_semester)
                ->whereHas('kelas.riwayatKelas', function ($query) use (
                    $presensiTarget,
                    $semesterAktif
                ) {
                    $query->where('id_siswa', $presensiTarget->id_siswa)
                        ->where('id_semester', $semesterAktif->id_semester);
                })
                ->exists();

            if (!$memilikiAkses) {
                return response()->json([
                    'message' => 'Kamu hanya dapat mengedit presensi siswa di kelas yang menjadi tanggung jawabmu.',
                ], 403);
            }
        }

        $waktuSekarang = now('Asia/Jakarta');

        $presensi = DB::transaction(function () use (
            $id_presensi,
            $validated,
            $user,
            $waktuSekarang
        ) {
            $presensi = Presensi::where('id_presensi', $id_presensi)
                ->lockForUpdate()
                ->first();

            if (!$presensi) {
                return null;
            }

            $statusLama = $presensi->status;
            $statusBaru = $validated['status'];

            if ($statusLama !== $statusBaru) {
                RiwayatPerubahanPresensi::create([
                    'id_presensi' => $presensi->id_presensi,
                    'id_user' => $user->id_user,
                    'status_sebelumnya' => $statusLama,
                    'status_baru' => $statusBaru,
                    'alasan_perubahan' => $validated['alasan_perubahan'],
                    'waktu_perubahan' => $waktuSekarang,
                ]);
            }

            $presensi->update([
                'status' => $statusBaru,
                'keterangan' => $validated['keterangan']
                    ?? $presensi->keterangan,
            ]);

            return $presensi->load(['siswa', 'izin', 'sakit']);
        });

        if (!$presensi) {
            return response()->json([
                'message' => 'Data presensi tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Data presensi berhasil diperbarui.',
            'data' => $presensi,
        ]);
    }

    public function riwayatPerubahan(
        Request $request,
        int $id_presensi
    ): JsonResponse {
        $user = $request->user();

        // Memastikan role memiliki hak akses.
        if (!in_array($user->role, [
            'SISWA',
            'GURU',
            'ADMIN',
            'BUDAYA_POSITIF',
            'KURIKULUM',
        ], true)) {
            return response()->json([
                'message' => 'Kamu tidak memiliki akses untuk melihat riwayat perubahan presensi ini.',
            ], 403);
        }

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
            $guru = $user->guru;

            if (!$guru) {
                return response()->json([
                    'message' => 'Akun guru belum terhubung dengan data guru.',
                ], 403);
            }

            // Mengambil semester aktif pada tahun ajaran aktif.
            $semesterAktif = Semester::where('aktif', true)
                ->whereHas('tahunAjaran', function ($query) {
                    $query->where('aktif', true);
                })
                ->first();

            if (!$semesterAktif) {
                return response()->json([
                    'message' => 'Semester dan tahun ajaran aktif belum dikonfigurasi.',
                ], 409);
            }

            // Memastikan siswa berada di kelas yang diampu guru
            // pada semester dan tahun ajaran aktif.
            $memilikiAkses = \App\Models\WaliKelas::where('id_guru', $guru->id_guru)
                ->where('id_semester', $semesterAktif->id_semester)
                ->whereHas('kelas.riwayatKelas', function ($query) use (
                    $presensi,
                    $semesterAktif
                ) {
                    $query->where('id_siswa', $presensi->id_siswa)
                        ->where('id_semester', $semesterAktif->id_semester);
                })
                ->exists();

            if (!$memilikiAkses) {
                return response()->json([
                    'message' => 'Kamu hanya dapat melihat riwayat perubahan presensi siswa di kelas yang menjadi tanggung jawabmu.',
                ], 403);
            }
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

        $hadir = (int) ($statistik['Hadir'] ?? 0);
        $terlambat = (int) ($statistik['Terlambat'] ?? 0);

        // Menghitung rata-rata persentase kehadiran harian.
        $statistikHarian = (clone $query)
            ->selectRaw("
                tanggal,
                SUM(
                    CASE
                        WHEN status IN ('Hadir', 'Terlambat') THEN 1
                        ELSE 0
                    END
                ) AS jumlah_hadir
            ")
            ->groupBy('tanggal')
            ->get();

        $persentase = 0;

        if ($jumlahSiswa > 0 && $statistikHarian->isNotEmpty()) {
            $totalPersentaseHarian = $statistikHarian->sum(
                fn ($item) => (
                    (int) $item->jumlah_hadir / $jumlahSiswa
                ) * 100
            );

            $persentase = round(
                $totalPersentaseHarian / $statistikHarian->count(),
                2
            );
        }

        return response()->json([
            'message' => 'Statistik presensi berhasil diambil.',
            'data' => [
                'periode' => $periode,
                'jumlah_siswa' => $jumlahSiswa,
                'total_catatan' => $totalCatatan,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'alpa' => (int) ($statistik['Alpa'] ?? 0),
                'izin' => (int) ($statistik['Izin'] ?? 0),
                'sakit' => (int) ($statistik['Sakit'] ?? 0),
                'persentase_kehadiran' => $persentase,
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

            // Menggabungkan status menjadi tiga kategori utama.
            $hadir = (int) ($jumlah['Hadir'] ?? 0)
                + (int) ($jumlah['Terlambat'] ?? 0);

            $izin = (int) ($jumlah['Izin'] ?? 0)
                + (int) ($jumlah['Sakit'] ?? 0);

            $alpa = (int) ($jumlah['Alpa'] ?? 0);

            $totalPresensi = $hadir + $izin + $alpa;

            // Menghitung persentase setiap kategori.
            $persentaseHadir = $totalPresensi > 0
                ? round(($hadir / $totalPresensi) * 100, 2)
                : 0;

            $persentaseIzin = $totalPresensi > 0
                ? round(($izin / $totalPresensi) * 100, 2)
                : 0;

            $persentaseAlpa = $totalPresensi > 0
                ? round(($alpa / $totalPresensi) * 100, 2)
                : 0;

            $riwayatKelasTerbaru = $item->riwayatKelas->first();

            return [
                'id_siswa' => $item->id_siswa,
                'nama' => $item->nama,
                'nisn' => $item->nisn,
                'kelas' => $riwayatKelasTerbaru?->kelas?->nama,
                'hadir' => $hadir,
                'izin' => $izin,
                'alpa' => $alpa,
                'total_presensi' => $totalPresensi,
                'persentase' => $persentaseHadir,
                'persentase_izin' => $persentaseIzin,
                'persentase_alpa' => $persentaseAlpa,
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
