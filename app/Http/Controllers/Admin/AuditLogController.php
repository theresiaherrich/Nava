<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = LogAktivitas::query()
            ->with('user:id_user,username,nama')
            ->latest('id');

        if ($aksi = $request->query('aksi')) {
            $query->where('aksi', $aksi);
        }

        if ($entitas = $request->query('entitas')) {
            $query->where('nama_entitas', $entitas);
        }

        if ($idUser = $request->query('id_user')) {
            $query->where('id_user', $idUser);
        }

        if ($dari = $request->query('dari')) {
            $query->whereDate('created_at', '>=', $dari);
        }

        if ($sampai = $request->query('sampai')) {
            $query->whereDate('created_at', '<=', $sampai);
        }

        return response()->json($query->paginate(50));
    }
}
