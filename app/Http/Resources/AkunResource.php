<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class AkunResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $label = config('siakad.roles');

        return [
            'id_user' => $this->id_user,
            'username' => $this->username,
            'nama' => $this->nama,
            'email' => $this->email,
            'status_akun' => $this->status_akun,
            'must_change_password' => $this->must_change_password,
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'roles' => collect($this->kodeRoles())->map(fn ($k) => ['kode' => $k, 'label' => $label[$k] ?? $k])->values(),
            'profil' => [
                'id_guru' => $this->guru?->id_guru,
                'id_siswa' => $this->siswa?->id_siswa,
                'id_orang_tua' => $this->orangTua?->id_orang_tua,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
