<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountService;
use App\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function __construct(
        private AccountService $accounts,
        private PasswordResetService $passwords,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = User::query()->with([
            'roles:id_role,kode,nama',
            'siswa:id_siswa,id_user,nama',
            'guru:id_guru,id_user,nama',
        ]);

        if ($q = trim((string) $request->query('q', ''))) {
            $query->where(fn ($w) => $w->where('nama', 'like', "%{$q}%")->orWhere('username', 'like', "%{$q}%"));
        }

        if ($status = $request->query('status')) {
            $query->where('status_akun', $status);
        }

        if ($peran = $request->query('peran')) {
            $query->whereHas('roles', fn ($r) => $r->where('kode', $peran));
        }

        return response()->json($query->orderBy('nama')->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:user,username'],
            'email' => ['nullable', 'email', 'max:100', 'unique:user,email'],
            'password' => ['nullable', 'string', 'min:8'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', 'exists:roles,kode'],
            'nip' => ['nullable', 'string', 'max:30', 'unique:guru,nip'],
            'status_kepegawaian' => ['nullable', Rule::in(['ASN', 'GTT'])],
        ]);

        $hasil = $this->accounts->buat(
            $data,
            $data['roles'],
            'aktif',
            ['nip' => $data['nip'] ?? null, 'status_kepegawaian' => $data['status_kepegawaian'] ?? null],
        );

        return response()->json([
            'message' => 'Akun dibuat.',
            'user' => $hasil['user']->only(['id_user', 'username', 'nama', 'status_akun']),
            // Hanya terisi bila dibuat otomatis; tidak akan ditampilkan lagi.
            'password_sementara' => $hasil['password'],
        ], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'nama' => ['sometimes', 'string', 'max:120'],
            'username' => ['sometimes', 'string', 'max:50', 'alpha_dash', Rule::unique('user', 'username')->ignore($user->id_user, 'id_user')],
            'email' => ['sometimes', 'nullable', 'email', 'max:100', Rule::unique('user', 'email')->ignore($user->id_user, 'id_user')],
        ]);

        return response()->json($this->accounts->ubahData($user, $data));
    }

    public function setRoles(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', 'exists:roles,kode'],
        ]);

        $this->accounts->ubahPeran($user, $data['roles']);

        return response()->json(['message' => 'Peran diperbarui.', 'roles' => $user->kodeRoles()]);
    }

    public function setujui(User $user): JsonResponse
    {
        $this->accounts->setujui($user);

        return response()->json(['message' => 'Akses disetujui.']);
    }

    public function setStatus(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'alasan' => ['required_if:status,nonaktif', 'string', 'max:500'],
        ]);

        $this->accounts->ubahStatus($user, $data['status'], $data['alasan'] ?? null);

        return response()->json(['message' => 'Status akun diperbarui.']);
    }

    public function resetPassword(User $user): JsonResponse
    {
        $plain = $this->passwords->resetOlehAdmin($user);

        return response()->json([
            'message' => 'Kata sandi sementara dibuat. Berikan kepada pengguna; tidak akan ditampilkan lagi.',
            'password_sementara' => $plain,
        ]);
    }

    public function linkProfil(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'tipe' => ['required', Rule::in(['siswa', 'guru'])],
            'id' => ['required', 'integer'],
        ]);

        $this->accounts->hubungkanProfil($user, $data['tipe'], (int) $data['id']);

        return response()->json(['message' => 'Profil terhubung ke akun.']);
    }
}
