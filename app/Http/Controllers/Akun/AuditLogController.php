<?php

namespace App\Http\Controllers\Akun;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'aksi' => ['nullable', 'string', 'max:50'],
            'nama_entitas' => ['nullable', 'string', 'max:50'],
            'entitas_id' => ['nullable', 'integer'],
            'id_user' => ['nullable', 'integer'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);

        $page = LogAktivitas::with('user:id_user,username,nama')
            ->when($request->aksi, fn ($q, $v) => $q->where('aksi', $v))
            ->when($request->nama_entitas, fn ($q, $v) => $q->where('nama_entitas', $v))
            ->when($request->entitas_id, fn ($q, $v) => $q->where('entitas_id', $v))
            ->when($request->id_user, fn ($q, $v) => $q->where('id_user', $v))
            ->when($request->dari, fn ($q, $v) => $q->where('created_at', '>=', $v))
            ->when($request->sampai, fn ($q, $v) => $q->where('created_at', '<', now()->parse($v)->addDay()))
            ->orderByDesc('id_log')
            ->paginate($this->perPage());

        return $this->paginated($page);
    }
}
