<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
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
    // LOGIN WEB (SESSION)
    // ==========================================

    public function loginWeb(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $validated['username'])->first();

        if (
            !$user ||
            !Hash::check($validated['password'], $user->password)
        ) {
            return response()->json([
                'message' => 'Username atau password salah.',
            ], 401);
        }

        Auth::login($user);

        $request->session()->regenerate();

        if ($user->must_change_password) {
            return response()->json([
                'message' => 'Silakan ganti password terlebih dahulu.',
                'redirect' => route('password.change'),
            ]);
        }

        $halamanRole = [
            'SISWA' => '/presensi/siswa',
            'GURU' => '/presensi/guru',
            'ADMIN' => '/presensi/admin/riwayat',
            'BUDAYA_POSITIF' => '/presensi/budaya-positif',
            'KURIKULUM' => '/presensi/kurikulum',
            'ORANG_TUA' => '/',
        ];

        return response()->json([
            'message' => 'Login berhasil.',
            'redirect' => $halamanRole[$user->role] ?? '/',
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
    // PENGAJUAN LUPA PASSWORD
    // ==========================================

    public function lupaPassword(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50'],
            'nisn' => ['required', 'string', 'max:30'],
        ]);

        $user = User::where(
            'username',
            trim($validated['username'])
        )->first();

        if (!$user || !in_array($user->role, ['SISWA', 'GURU', 'ORANG_TUA'])) {
            return response()->json([
                'message' => 'Data akun tidak cocok. Periksa kembali informasi yang dimasukkan.',
            ], 422);
        }

        // Verifikasi identitas sesuai role.
        if ($user->role === 'SISWA') {
            $profil = $user->siswa;

            $identitasCocok = $profil &&
                $profil->nisn === trim($validated['nisn']);

        } elseif ($user->role === 'GURU') {
            $profil = $user->guru;

            $identitasCocok = $profil &&
                $profil->nip === trim($validated['nisn']);

        } else {
            $identitasCocok = \App\Models\OrangTua::where(
                'id_user',
                $user->id_user
            )
                ->whereHas('siswa', function ($query) use ($validated) {
                    $query->where(
                        'nisn',
                        trim($validated['nisn'])
                    );
                })
                ->exists();
        }

        // Buat token reset password.
        $token = bin2hex(random_bytes(32));

        // Hapus token lama milik akun tersebut.
        DB::table('reset_password_tokens')
            ->where('id_user', $user->id_user)
            ->delete();

        // Simpan hash token dengan masa berlaku 15 menit.
        DB::table('reset_password_tokens')->insert([
            'id_user' => $user->id_user,
            'token' => Hash::make($token),
            'expires_at' => now()->addMinutes(15),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Data berhasil diverifikasi.',
            'reset_url' => url('/reset-password/' . $token),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        // Cari token berdasarkan seluruh token yang masih aktif.
        $resetToken = DB::table('reset_password_tokens')
            ->where('expires_at', '>', now())
            ->get()
            ->first(function ($item) use ($validated) {
                return Hash::check($validated['token'], $item->token);
            });

        if (!$resetToken) {
            return response()->json([
                'message' => 'Link reset password tidak valid atau sudah kedaluwarsa. Silakan ajukan ulang.',
            ], 422);
        }

        $user = User::find($resetToken->id_user);

        if (!$user || !in_array($user->role, ['SISWA', 'GURU'])) {
            return response()->json([
                'message' => 'Akun tidak ditemukan atau tidak dapat direset.',
            ], 422);
        }

        // Perbarui password.
        $user->password = $validated['password'];
        $user->must_change_password = false;
        $user->save();

        // Hapus token agar tidak dapat digunakan kembali.
        DB::table('reset_password_tokens')
            ->where('id_user', $user->id_user)
            ->delete();

        return response()->json([
            'message' => 'Password berhasil diperbarui. Silakan login kembali.',
        ]);
    }


    // ==========================================
    // GANTI PASSWORD
    // ==========================================

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'string',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'current_password.required' =>
                'Password awal wajib diisi.',

            'password.min' =>
                'Password baru minimal 8 karakter.',

            'password.confirmed' =>
                'Konfirmasi password baru tidak cocok.',
        ]);

        $user = $request->user();

        // Periksa password awal.
        if (!Hash::check(
            $validated['current_password'],
            $user->password
        )) {
            return response()->json([
                'message' => 'Password awal yang dimasukkan salah.',
            ], 422);
        }

        // Password baru harus berbeda.
        if (Hash::check(
            $validated['password'],
            $user->password
        )) {
            return response()->json([
                'message' => 'Password baru harus berbeda dari password lama.',
            ], 422);
        }

        // Simpan password baru.
        $user->password = $validated['password'];
        $user->must_change_password = false;
        $user->save();

        return response()->json([
            'message' => 'Password berhasil diperbarui.',
            'data' => [
                'must_change_password' => false,
            ],
        ]);
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

    public function logoutWeb(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda berhasil logout.');
    }
}
