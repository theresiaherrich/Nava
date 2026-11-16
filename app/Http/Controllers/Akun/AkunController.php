<?php

namespace App\Http\Controllers\Akun;

use App\Http\Controllers\Controller;
use App\Http\Requests\Akun\SetPeranRequest;
use App\Http\Requests\Akun\SetStatusRequest;
use App\Http\Requests\Akun\StoreAkunRequest;
use App\Http\Requests\Akun\UpdateAkunRequest;
use App\Http\Resources\AkunResource;
use App\Models\Role;
use App\Models\User;
use App\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Kelola akun & peran (SUPERADMIN penuh; ADMIN sesuai config('siakad.role_manageable')). */
class AkunController extends Controller
{
    public function __construct(private AccountService $akun) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'string'],
            'status_akun' => ['nullable', 'in:aktif,nonaktif,belum_aktif'],
        ]);

        $page = User::query()
            ->with(['roles', 'guru:id_guru,id_user', 'siswa:id_siswa,id_user', 'orangTua:id_orang_tua,id_user'])
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('username', 'like', "%{$s}%")->orWhere('nama', 'like', "%{$s}%")))
            ->when($request->role, fn ($q, $r) => $q->whereHas('roles', fn ($w) => $w->where('kode', $r)))
            ->when($request->status_akun, fn ($q, $s) => $q->where('status_akun', $s))
            ->orderBy('nama')
            ->paginate($this->perPage());

        $boleh = $this->akun->peranDapatDikelola();

        return $this->paginated($page, fn (User $u) => AkunResource::make($u)->resolve() + [
            'dapat_dikelola' => $boleh !== [] && array_diff(array_diff($u->kodeRoles(), config('siakad.role_otomatis')), $boleh) === [],
        ]);
    }

    public function show(User $user): JsonResponse
    {
        return $this->ok(AkunResource::make($user->load('roles'))->resolve());
    }

    public function store(StoreAkunRequest $request): JsonResponse
    {
        $hasil = $this->akun->buat(
            $request->safe()->except(['roles', 'id_guru', 'id_siswa', 'id_orang_tua']),
            $request->input('roles'),
            array_filter($request->only(['id_guru', 'id_siswa', 'id_orang_tua'])),
        );

        return $this->created([
            'akun' => AkunResource::make($hasil['user'])->resolve(),
            // Hanya muncul sekali; tidak disimpan dalam bentuk asli di mana pun.
            'password_awal' => $hasil['password'],
        ], 'Akun berhasil dibuat.');
    }

    public function update(UpdateAkunRequest $request, User $user): JsonResponse
    {
        return $this->ok(AkunResource::make($this->akun->ubah($user, $request->validated()))->resolve(), 'Akun diperbarui.');
    }

    public function setPeran(SetPeranRequest $request, User $user): JsonResponse
    {
        $user = $this->akun->ubahPeran($user, $request->input('roles'), array_filter($request->only(['id_guru', 'id_siswa', 'id_orang_tua'])));

        return $this->ok(AkunResource::make($user)->resolve(), 'Peran akun diperbarui.');
    }

    public function setStatus(SetStatusRequest $request, User $user): JsonResponse
    {
        $user = $this->akun->ubahStatus($user, $request->string('status_akun'), $request->input('alasan'));

        return $this->ok(AkunResource::make($user)->resolve(), 'Status akun diperbarui.');
    }

    public function resetPassword(User $user): JsonResponse
    {
        return $this->ok(['password_sementara' => $this->akun->resetPassword($user)], 'Kata sandi direset. Pengguna wajib menggantinya saat login.');
    }

    public function roles(): JsonResponse
    {
        $boleh = $this->akun->peranDapatDikelola();
        $otomatis = config('siakad.role_otomatis');
        $label = config('siakad.roles');

        return $this->ok(Role::orderBy('id_role')->get()->map(fn (Role $r) => [
            'kode' => $r->kode,
            'label' => $label[$r->kode] ?? $r->nama,
            'otomatis' => in_array($r->kode, $otomatis, true),
            'dapat_diberikan' => in_array($r->kode, $boleh, true),
        ]));
    }
}
