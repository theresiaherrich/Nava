<?php

namespace App\Http\Controllers\A3_BK\Concerns;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait RecordsA3Activity
{
    protected function recordA3Activity(string $action, string $entity, int $entityId): void
    {
        DB::table('log_aktivitas')->insert([
            'id_user' => Auth::id(),
            'aksi' => $action,
            'nama_entitas' => $entity,
            'entitas_id' => $entityId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
