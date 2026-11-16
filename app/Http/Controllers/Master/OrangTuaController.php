<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\HubungkanOrangTuaRequest;
use App\Http\Requests\Master\OrangTuaRequest;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Data orang tua dan relasi orang tua ↔ anak (dikelola Operator, dianggap terverifikasi). */
class OrangTuaController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100']]);

        $page = OrangTua::with('anak:siswa.id_siswa,nis,nama')
            ->when($request->q, fn ($q, $s) => $q->where('nama', 'like', "%{$s}%")->orWhere('no_telepon', 'like', "%{$s}%"))
            ->orderBy('nama')
            ->paginate($this->perPage());

        return $this->paginated($page, fn (OrangTua $o) => $this->bentuk($o));
    }

    public function store(OrangTuaRequest $request): JsonResponse
    {
        $o = DB::transaction(function () use ($request) {
            $o = OrangTua::create($request->validated());
            $this->audit->catat('buat_orang_tua', 'orang_tua', $o->id_orang_tua, null, $request->validated());

            return $o;
        });

        return $this->created($this->bentuk($o->load('anak')), 'Orang tua ditambahkan.');
    }

    public function update(OrangTuaRequest $request, OrangTua $orangTua): JsonResponse
    {
        DB::transaction(function () use ($request, $orangTua) {
            $sebelum = $orangTua->only(array_keys($request->validated()));
            $orangTua->update($request->validated());
            $this->audit->catat('ubah_orang_tua', 'orang_tua', $orangTua->id_orang_tua, $sebelum, $request->validated());
        });

        return $this->ok($this->bentuk($orangTua->fresh('anak')), 'Data orang tua diperbarui.');
    }

    public function hubungkan(HubungkanOrangTuaRequest $request, Siswa $siswa): JsonResponse
    {
        DB::transaction(function () use ($request, $siswa) {
            $siswa->orangTua()->syncWithoutDetaching([$request->integer('id_orang_tua') => ['hubungan' => $request->string('hubungan')]]);
            $this->audit->catat('hubungkan_orang_tua', 'siswa', $siswa->id_siswa, null, $request->validated());
        });

        return $this->ok($siswa->orangTua()->get()->map(fn ($o) => ['id_orang_tua' => $o->id_orang_tua, 'nama' => $o->nama, 'hubungan' => $o->pivot->hubungan]), 'Relasi orang tua disimpan.');
    }

    public function putuskan(Siswa $siswa, OrangTua $orangTua): JsonResponse
    {
        DB::transaction(function () use ($siswa, $orangTua) {
            $siswa->orangTua()->detach($orangTua->id_orang_tua);
            $this->audit->catat('putuskan_orang_tua', 'siswa', $siswa->id_siswa, ['id_orang_tua' => $orangTua->id_orang_tua]);
        });

        return $this->ok(null, 'Relasi orang tua dihapus.');
    }

    private function bentuk(OrangTua $o): array
    {
        return $o->only(['id_orang_tua', 'nama', 'jenis_kelamin', 'no_telepon', 'alamat']) + [
            'punya_akun' => $o->id_user !== null,
            'anak' => $o->anak->map(fn ($s) => ['id_siswa' => $s->id_siswa, 'nis' => $s->nis, 'nama' => $s->nama, 'hubungan' => $s->pivot->hubungan])->values(),
        ];
    }
}
