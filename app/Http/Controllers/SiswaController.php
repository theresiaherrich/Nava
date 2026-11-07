<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SiswaController extends Controller
{
    public function index(): JsonResponse
    {
        $siswa = Siswa::with('user')
            ->orderBy('nama')
            ->get();

        return response()->json([
            'message' => 'Data siswa berhasil diambil.',
            'data' => $siswa,
        ]);
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

            return Siswa::create([
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
        });

        return response()->json([
            'message' => 'Data siswa berhasil didaftarkan.',
            'data' => $siswa,
        ], 201);
    }
}