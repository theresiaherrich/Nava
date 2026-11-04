<?php

namespace App\Services;

use App\Models\LogAktivitas;
use Illuminate\Support\Arr;

class AuditService
{
    public static function record(string $action, string $table, ?int $id, ?array $before = null, ?array $after = null, ?string $reason = null, ?int $actorId = null): void
    {
        // Jangan pernah memasukkan request->all(): bisa berisi password/token.
        $sensitive = ['password', 'password_confirmation', 'remember_token', 'token'];
        LogAktivitas::create([
            'user_id' => $actorId ?? auth()->id(), 'aksi' => $action, 'nama_entitas' => $table,
            'entitas_id' => $id, 'alasan' => $reason,
            'data_sebelum' => $before === null ? null : Arr::except($before, $sensitive),
            'data_sesudah' => $after === null ? null : Arr::except($after, $sensitive),
        ]);
    }
}
