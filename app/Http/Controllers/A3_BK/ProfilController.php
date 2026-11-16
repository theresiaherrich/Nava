<?php

namespace App\Http\Controllers\A3_BK;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function show(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $user->loadMissing(['guru', 'siswa.riwayatKelasAktif.kelas']);

        $role = strtoupper($user->role);
        $semester = Semester::query()
            ->where('aktif', true)
            ->orderByDesc('id_semester')
            ->first();

        $profile = match ($role) {
            'ADMIN' => [
                'name' => $user->username,
                'identifier_label' => 'Nama pengguna',
                'identifier' => $user->username,
                'job_title' => 'Administrator',
                'email' => null,
                'phone' => null,
                'address' => null,
                'class_name' => null,
                'grade' => null,
            ],
            'GURU', 'GURU_BK' => $this->guruProfile($user),
            'SISWA' => $this->siswaProfile($user),
            default => abort(403),
        };

        $profileRoute = $this->profileRoute($role);
        $logPassword = DB::table('log_aktivitas')
            ->where('id_user', $user->id_user)
            ->where('aksi', 'ubah_password')
            ->orderByDesc('created_at')
            ->first();

        return view('bk.profil', compact('user', 'role', 'profile', 'profileRoute', 'semester', 'logPassword'));
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $redirectRoute = match (strtoupper($user->role)) {
            'ADMIN' => $this->updateAdmin($request, $user),
            'GURU', 'GURU_BK' => $this->updateGuru($request, $user),
            'SISWA' => $this->updateSiswa($request, $user),
            default => abort(403),
        };

        return redirect()->route($redirectRoute)->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $role = strtoupper($user->role);
        $redirectRoute = $this->profileRoute($role);

        $validated = $request->validate([
            'password_lama' => ['required', 'string'],
            'password_baru' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['password_lama'], $user->password)) {
            return back()->withErrors(['password_lama' => 'Kata sandi lama tidak sesuai.']);
        }

        $user->forceFill([
            'password' => Hash::make($validated['password_baru']),
            'password_changed_at' => now(),
        ])->save();

        DB::table('log_aktivitas')->insert([
            'id_user' => $user->id_user,
            'aksi' => 'ubah_password',
            'nama_entitas' => 'user',
            'entitas_id' => $user->id_user,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route($redirectRoute)->with('success', 'Kata sandi berhasil diubah.');
    }

    /**
     * @return array{name: string, identifier_label: string, identifier: string, job_title: string, email: ?string, phone: ?string, address: ?string, class_name: null, grade: null}
     */
    private function guruProfile(User $user): array
    {
        $guru = $user->guru;

        abort_unless($guru, 403, 'Data guru tidak ditemukan.');

        return [
            'name' => $guru->nama,
            'identifier_label' => 'NIP',
            'identifier' => $guru->nip ?? '-',
            'job_title' => 'Guru Bimbingan dan Konseling',
            'email' => $guru->email,
            'phone' => $guru->no_telepon,
            'address' => $guru->alamat,
            'class_name' => null,
            'grade' => null,
        ];
    }

    /**
     * @return array{name: string, identifier_label: string, identifier: string, job_title: string, email: null, phone: null, address: ?string, class_name: ?string, grade: ?string}
     */
    private function siswaProfile(User $user): array
    {
        $siswa = $user->siswa;

        abort_unless($siswa, 403, 'Data siswa tidak ditemukan.');

        $riwayat = $siswa->riwayatKelasAktif;

        return [
            'name' => $siswa->nama,
            'identifier_label' => 'NIS',
            'identifier' => $siswa->nis,
            'job_title' => 'Siswa',
            'email' => null,
            'phone' => null,
            'address' => $siswa->alamat,
            'class_name' => $riwayat?->kelas?->nama,
            'grade' => $riwayat?->kelas?->tingkat,
        ];
    }

    private function updateAdmin(Request $request, User $user): string
    {
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('user', 'username')->ignore($user->id_user, 'id_user'),
            ],
        ]);

        $user->update($validated);

        return 'admin.profil';
    }

    private function updateGuru(Request $request, User $user): string
    {
        $guru = $user->guru;
        abort_unless($guru, 403, 'Data guru tidak ditemukan.');

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:100'],
            'no_telepon' => ['nullable', 'string', 'max:25'],
            'alamat' => ['nullable', 'string'],
        ]);

        $guru->update($validated);

        return $this->profileRoute(strtoupper($user->role));
    }

    private function updateSiswa(Request $request, User $user): string
    {
        $siswa = $user->siswa;
        abort_unless($siswa, 403, 'Data siswa tidak ditemukan.');

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'alamat' => ['nullable', 'string'],
        ]);

        $siswa->update($validated);

        return 'siswa.profil';
    }

    private function profileRoute(string $role): string
    {
        return match ($role) {
            'ADMIN' => 'admin.profil',
            'GURU', 'GURU_BK' => 'bk.profil',
            'SISWA' => 'siswa.profil',
            default => abort(403),
        };
    }
}
