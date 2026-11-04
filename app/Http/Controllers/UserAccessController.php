<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\LogAktivitas;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserAccessController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $data = $request->validate(['is_active' => ['sometimes', 'boolean'], 'page' => ['sometimes', 'integer', 'min:1']]);

        return response()->json(User::with('roles')->when(array_key_exists('is_active', $data), fn ($q) => $q->where('is_active', $data['is_active']))->orderByDesc('id')->paginate(20));
    }

    public function roles(): JsonResponse
    {
        return response()->json(['data' => Role::orderBy('name')->get()]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        abort_if($user->is($request->user()), 422, 'Ubah akses akun lain; akses akun sendiri tidak boleh diubah di sini.');
        $data = $request->validate([
            'roles' => ['required', 'array', 'min:1'], 'roles.*' => ['required', 'string', 'distinct', Rule::exists('roles', 'name')],
            'is_active' => ['required', 'boolean'], 'alasan' => ['required', 'string', 'min:5', 'max:1000'],
        ]);
        DB::transaction(function () use ($user, $data) {
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $before = ['is_active' => $user->is_active, 'roles' => $user->roles()->pluck('name')->all()];
            $user->forceFill(['is_active' => $data['is_active']])->save();
            $user->roles()->sync(Role::whereIn('name', $data['roles'])->pluck('id'));
            AuditService::record('update_access', 'users', $user->id, $before, ['is_active' => $user->is_active, 'roles' => $data['roles']], $data['alasan']);
        });

        return response()->json(['data' => $user->fresh()->load('roles')]);
    }

    public function linkProfile(Request $request, User $user): JsonResponse
    {
        $data = $request->validate(['jenis' => ['required', Rule::in(['siswa', 'guru'])], 'profil_id' => ['required', 'integer'], 'alasan' => ['required', 'string', 'min:5', 'max:1000']]);
        DB::transaction(function () use ($user, $data) {
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            abort_unless($user->hasAnyRole([$data['jenis']]), 422, 'Role akun harus sesuai jenis profil terlebih dahulu.');
            $model = $data['jenis'] === 'siswa' ? Siswa::class : Guru::class;
            $profile = $model::whereKey($data['profil_id'])->lockForUpdate()->firstOrFail();
            abort_if($profile->user_id !== null && $profile->user_id !== $user->id, 409, 'Profil sudah terhubung ke akun lain.');
            abort_if($model::where('user_id', $user->id)->where('id', '!=', $profile->id)->exists(), 409, 'Akun sudah terhubung ke profil lain.');
            $before = $profile->toArray();
            $profile->forceFill(['user_id' => $user->id])->save();
            AuditService::record('link_profile', $profile->getTable(), $profile->id, $before, $profile->toArray(), $data['alasan']);
        });

        return response()->json(['message' => 'Profil berhasil dihubungkan.']);
    }

    public function logs(): JsonResponse
    {
        return response()->json(LogAktivitas::orderByDesc('id')->paginate(20));
    }
}
