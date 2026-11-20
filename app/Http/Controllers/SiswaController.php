<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\User;
use App\Models\QrSiswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Exports\TemplateSiswaExport;
use App\Exports\KredensialSiswaExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SiswaImport;
use Illuminate\Support\Facades\Validator;

class SiswaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'SISWA') {
            $siswa = Siswa::with('user')
                ->where('id_user', $user->id_user)
                ->first();

            if (!$siswa) {
                return response()->json([
                    'message' => 'Data siswa tidak ditemukan.',
                ], 404);
            }

            return response()->json([
                'message' => 'Data siswa berhasil diambil.',
                'data' => [$siswa],
            ]);
        }

        if (in_array($user->role, [
            'ADMIN',
            'BUDAYA_POSITIF',
            'KURIKULUM',
        ], true)) {
            $siswa = Siswa::with('user')
                ->orderBy('nama')
                ->get();

            return response()->json([
                'message' => 'Data siswa berhasil diambil.',
                'data' => $siswa,
            ]);
        }

        if ($user->role === 'GURU') {
            return response()->json([
                'message' => 'Akses data siswa berdasarkan penugasan wali kelas belum tersedia karena tabel integrasi kelas belum diterapkan.',
            ], 409);
        }

        return response()->json([
            'message' => 'Kamu tidak memiliki akses untuk melihat data siswa.',
        ], 403);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:user,username'],
            'password' => ['required', 'string', 'min:8'],
            'nis' => ['required', 'string', 'max:30', 'unique:siswa,nis'],
            'nisn' => ['nullable', 'string', 'max:30', 'unique:siswa,nisn'],
            'nama' => ['required', 'string', 'max:120'],
            'agama' => ['nullable', 'string', 'max:30'],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'tahun_masuk' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'urutan_masuk' => ['nullable', 'integer', 'min:1'],
            'alamat' => ['nullable', 'string'],
        ]);

        $siswa = DB::transaction(function () use ($validated) {
            $user = User::create([
                'username' => $validated['username'],
                'password' => $validated['password'],
                'must_change_password' => true,
                'role' => 'SISWA',
            ]);

            $siswa = Siswa::create([
                'id_user' => $user->id_user,
                'nis' => $validated['nis'],
                'nisn' => $validated['nisn'] ?? null,
                'nama' => $validated['nama'],
                'agama' => $validated['agama'] ?? null,
                'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
                'tahun_masuk' => $validated['tahun_masuk'] ?? null,
                'urutan_masuk' => $validated['urutan_masuk'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
            ]);

            QrSiswa::create([
                'id_siswa' => $siswa->id_siswa,
                'kode_qr' => (string) Str::uuid(),
            ]);

            return $siswa;
        });

        return response()->json([
            'message' => 'Data siswa berhasil didaftarkan.',
            'data' => $siswa,
        ], 201);
    }

    public function downloadTemplate()
    {
    return Excel::download(
        new TemplateSiswaExport,
        'template_data_siswa.xlsx'
    );
    }

    public function importExcel(Request $request)
    {

    $kredensial = [];

    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls|max:5120',
    ]);

    $import = new SiswaImport();

    Excel::import($import, $request->file('file'));

    if (!$import->validateHeaders()) {
        return response()->json([
            'message' => 'Format kolom Excel tidak sesuai template.',
        ], 422);
    }
    $errors = $import->validateRows();

    if (!empty($errors)) {
        return response()->json([
            'message' => 'Terdapat kesalahan pada data Excel.',
            'errors' => $errors,
        ], 422);
    }

    DB::transaction(function () use ($import, &$kredensial) {
    foreach ($import->getRows() as $index => $row) {
        $data = $row->toArray();

        // Membuat akun user
        $user = User::create([
            'nama' => trim($data['nama']),
            'username' => $import->usernames[$index],
            'password' => '12345',
            'must_change_password' => true,
            'role' => 'SISWA',
        ]);

        $kredensial[] = [
            'nama' => trim($data['nama']),
            'nis' => trim($data['nis']),
            'username' => $import->usernames[$index],
            'password_awal' => '12345',
        ];

        // Membuat data siswa
        $siswa = Siswa::create([
            'id_user' => $user->id_user,
            'nama' => trim($data['nama']),
            'nis' => trim($data['nis']),
            'nisn' => !empty($data['nisn']) ? trim($data['nisn']) : null,
            'agama' => $data['agama'] ?? null,
            'jenis_kelamin' => !empty($data['jenis_kelamin'])
                ? strtoupper(trim($data['jenis_kelamin']))
                : null,
            'alamat' => $data['alamat'] ?? null,
            'tahun_masuk' => (int) $data['tahun_masuk'],
            'urutan_masuk' => (int) $data['urutan_masuk'],
        ]);

        // Membuat QR Code siswa
        QrSiswa::create([
            'id_siswa' => $siswa->id_siswa,
            'kode_qr' => (string) Str::uuid(),
        ]);
    }
        });

        return Excel::download(
            new KredensialSiswaExport($kredensial),
            'kredensial_akun_siswa.xlsx'
        );
    }

}