<?php

namespace App\Services;

use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

/**
 * Satu-satunya pintu penulisan log_aktivitas. Setiap aksi penting mencatat
 * pelaku, waktu (created_at), dan alasan.
 */
class AuditService
{
    private const WAJIB_ALASAN = ['buka_koreksi', 'nonaktifkan_akun'];

    public function catat(
        string $aksi,
        string $entitas,
        int|string $entitasId,
        ?string $alasan = null,
        ?array $sebelum = null,
        ?array $sesudah = null,
        ?int $idUser = null,
    ): LogAktivitas {
        $pelaku = $idUser ?? Auth::id();

        if (! $pelaku) {
            throw new InvalidArgumentException('Audit membutuhkan pelaku.');
        }

        if (in_array($aksi, self::WAJIB_ALASAN, true) && blank($alasan)) {
            throw new InvalidArgumentException("Aksi {$aksi} wajib menyertakan alasan.");
        }

        return LogAktivitas::create([
            'id_user' => $pelaku,
            'aksi' => $aksi,
            'nama_entitas' => $entitas,
            'entitas_id' => (int) $entitasId,
            'alasan' => $alasan,
            'data_sebelum' => $sebelum,
            'data_sesudah' => $sesudah,
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
