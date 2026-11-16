<?php

namespace App\Http\Controllers\Master;

use App\Exceptions\AturanBisnisException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\GuruRequest;
use App\Models\Guru;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuruController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status_kepegawaian' => ['nullable', 'in:ASN,GTT'],
            'aktif' => ['nullable', 'boolean'],
            'tanpa_akun' => ['nullable', 'boolean'],
        ]);

        $page = Guru::query()
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('nama', 'like', "%{$s}%")->orWhere('nip', 'like', "%{$s}%")))
            ->when($request->status_kepegawaian, fn ($q, $v) => $q->where('status_kepegawaian', $v))
            ->when($request->has('aktif'), fn ($q) => $q->where('aktif', $request->boolean('aktif')))
            ->when($request->boolean('tanpa_akun'), fn ($q) => $q->whereNull('id_user'))
            ->orderBy('nama')
            ->paginate($this->perPage());

        return $this->paginated($page, fn (Guru $g) => $this->bentuk($g));
    }

    public function show(Guru $guru): JsonResponse
    {
        return $this->ok($this->bentuk($guru));
    }

    public function store(GuruRequest $request): JsonResponse
    {
        $guru = DB::transaction(function () use ($request) {
            $g = Guru::create($request->validated());
            $this->audit->catat('buat_guru', 'guru', $g->id_guru, null, $request->validated());

            return $g;
        });

        return $this->created($this->bentuk($guru->fresh()), 'Guru ditambahkan.');
    }

    public function update(GuruRequest $request, Guru $guru): JsonResponse
    {
        $data = $request->validated();
        if (($data['status_kepegawaian'] ?? $guru->status_kepegawaian) === 'ASN' && blank($data['nip'] ?? $guru->nip)) {
            throw new AturanBisnisException('NIP wajib diisi untuk guru ASN.', 'VALIDATION_ERROR', 422, ['nip' => ['NIP wajib diisi untuk guru ASN.']]);
        }

        DB::transaction(function () use ($data, $guru) {
            $sebelum = $guru->only(array_keys($data));
            $guru->update($data);
            $this->audit->catat('ubah_guru', 'guru', $guru->id_guru, $sebelum, $data);
        });

        return $this->ok($this->bentuk($guru->fresh()), 'Data guru diperbarui.');
    }

    private function bentuk(Guru $g): array
    {
        return $g->only(['id_guru', 'nip', 'status_kepegawaian', 'nama', 'jenis_kelamin', 'id_agama', 'no_telepon', 'aktif']) + [
            'punya_akun' => $g->id_user !== null,
        ];
    }
}
