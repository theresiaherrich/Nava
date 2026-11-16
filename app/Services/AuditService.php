<?php

namespace App\Services;

use App\Models\LogAktivitas;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

/**
 * Satu-satunya pintu penulisan log_aktivitas: aktor, aksi, target, data lama/baru.
 * Field sensitif selalu dibuang sebelum disimpan.
 */
class AuditService
{
    private const SENSITIF = ['password', 'password_confirmation', 'password_baru', 'password_lama', 'remember_token', 'token'];

    private const WAJIB_ALASAN = ['buka_koreksi', 'cabut_rapor', 'minta_revisi'];

    public function catat(
        string $aksi,
        string $entitas,
        int|string|null $entitasId,
        ?array $sebelum = null,
        ?array $sesudah = null,
        ?string $alasan = null,
        ?int $idAktor = null,
    ): LogAktivitas {
        if (in_array($aksi, self::WAJIB_ALASAN, true) && blank($alasan)) {
            throw new InvalidArgumentException("Aksi {$aksi} wajib menyertakan alasan.");
        }

        return LogAktivitas::create([
            'id_user' => $idAktor ?? Auth::id(),
            'aksi' => $aksi,
            'nama_entitas' => $entitas,
            'entitas_id' => $entitasId === null ? null : (int) $entitasId,
            'alasan' => $alasan,
            'data_sebelum' => $sebelum === null ? null : $this->bersihkan($sebelum),
            'data_sesudah' => $sesudah === null ? null : $this->bersihkan($sesudah),
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
        ]);
    }

    private function bersihkan(array $data): array
    {
        return Arr::except($data, self::SENSITIF);
    }
}
