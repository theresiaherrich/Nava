<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ==========================================
    // LOGIN
    // ==========================================

    public function login(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where(
            'username',
            $validated['username']
        )->first();

        if (
            !$user ||
            !Hash::check($validated['password'], $user->password)
        ) {
            return response()->json([
                'message' => 'Username atau password salah.',
            ], 401);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => [
                'user' => [
                    'id_user' => $user->id_user,
                    'username' => $user->username,
                    'role' => $user->role,
                    'must_change_password' => $user->must_change_password,
                ],
                'token' => $token,
            ],
        ]);
    }


    // ==========================================
    // REGISTER ORANG TUA
    // ==========================================

    public function register(Request $request)
    {
        // Normalisasi nomor telepon.
        $telepon = preg_replace(
            '/[\s-]/',
            '',
            $request->input('telepon', '')
        );

        $request->merge([
            'telepon' => $telepon,
        ]);

        // Validasi data dari frontend.
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:50',
                'unique:user,username',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
            ],

            'nama' => [
                'required',
                'string',
                'max:120',
            ],

            'jenis_kelamin' => [
                'required',
                'in:L,P',
            ],

            'telepon' => [
                'required',
                'string',
                'max:25',
                'regex:/^(\+62|62|0)8\d{7,12}$/',
            ],

            'alamat' => [
                'required',
                'string',
            ],

            'hubungan' => [
                'required',
                'in:Ayah,Ibu,Wali',
            ],

            'username_siswa' => [
                'required',
                'string',
                'max:50',
            ],

            'nisn' => [
                'required',
                'digits:10',
            ],
        ], [
            'username.unique' =>
                'Username sudah digunakan. Silakan gunakan username lain.',

            'password.min' =>
                'Password minimal 8 karakter.',

            'jenis_kelamin.in' =>
                'Jenis kelamin tidak valid.',

            'telepon.regex' =>
                'Format nomor telepon tidak valid.',

            'hubungan.in' =>
                'Hubungan dengan siswa tidak valid.',

            'nisn.digits' =>
                'NISN harus terdiri dari 10 digit angka.',
        ]);

        // Seluruh proses penyimpanan dilakukan dalam transaksi.
        $hasil = DB::transaction(function () use ($validated) {

            // 1. Cocokkan username siswa dan NISN.
            $siswa = DB::table('siswa')
                ->join(
                    'user',
                    'siswa.id_user',
                    '=',
                    'user.id_user'
                )
                ->where(
                    'user.username',
                    $validated['username_siswa']
                )
                ->where(
                    'user.role',
                    'SISWA'
                )
                ->where(
                    'siswa.nisn',
                    $validated['nisn']
                )
                ->select(
                    'siswa.id_siswa',
                    'siswa.nama'
                )
                ->first();

            if (!$siswa) {
                throw ValidationException::withMessages([
                    'username_siswa' => [
                        'Username siswa dan NISN tidak cocok. Periksa kembali data siswa.',
                    ],
                ]);
            }

            // 2. Periksa apakah hubungan tersebut sudah terdaftar.
            $relasiSudahAda = DB::table('orang_tua')
                ->where('id_siswa', $siswa->id_siswa)
                ->where('hubungan', $validated['hubungan'])
                ->exists();

            if ($relasiSudahAda) {
                throw ValidationException::withMessages([
                    'hubungan' => [
                        'Data orang tua dengan hubungan tersebut sudah terdaftar untuk siswa ini.',
                    ],
                ]);
            }

            // 3. Buat akun orang tua.
            $user = User::create([
                'username' => $validated['username'],

                // Model User memiliki hashed cast.
                'password' => $validated['password'],

                'must_change_password' => false,

                'role' => 'ORANG_TUA',
            ]);

            // 4. Simpan hubungan orang tua dengan siswa.
            DB::table('orang_tua')->insert([
                'id_siswa' => $siswa->id_siswa,
                'hubungan' => $validated['hubungan'],
                'id_user' => $user->id_user,

                'nama' => $validated['nama'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'no_telepon' => $validated['telepon'],
                'alamat' => $validated['alamat'],

                'status_verifikasi' => 'pending',

                'diverifikasi_oleh' => null,
                'waktu_verifikasi' => null,
            ]);

            return [
                'id_user' => $user->id_user,
                'username' => $user->username,
                'nama_siswa' => $siswa->nama,
            ];
        });

        return response()->json([
            'message' =>
                'Pendaftaran berhasil! Akun Anda sedang menunggu verifikasi petugas sekolah.',

            'data' => $hasil,

        ], 201);
    }


    // ==========================================
    // LOGOUT
    // ==========================================

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }
}
