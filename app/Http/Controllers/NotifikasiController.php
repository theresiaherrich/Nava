<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Dropdown notifikasi; setiap pengguna hanya melihat notifikasinya sendiri. */
class NotifikasiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate(['belum_dibaca' => ['nullable', 'boolean']]);

        $page = Notifikasi::where('id_user', Auth::id())
            ->when($request->boolean('belum_dibaca'), fn ($q) => $q->whereNull('dibaca_at'))
            ->orderByDesc('id_notifikasi')->paginate($this->perPage());

        $res = $this->paginated($page);
        $res->setData($res->getData(true) + ['jumlah_belum_dibaca' => Notifikasi::where('id_user', Auth::id())->whereNull('dibaca_at')->count()]);

        return $res;
    }

    public function baca(int $id): JsonResponse
    {
        $n = Notifikasi::where('id_user', Auth::id())->findOrFail($id);
        $n->dibaca_at ??= now();
        $n->save();

        return $this->ok($n);
    }

    public function bacaSemua(): JsonResponse
    {
        $jumlah = Notifikasi::where('id_user', Auth::id())->whereNull('dibaca_at')->update(['dibaca_at' => now()]);

        return $this->ok(['ditandai' => $jumlah], 'Semua notifikasi ditandai dibaca.');
    }
}
