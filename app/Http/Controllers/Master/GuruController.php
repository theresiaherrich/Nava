<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request): JsonResponse
    {
        $query = Guru::query()->orderBy('nama');

        if ($q = trim((string) $request->query('q', ''))) {
            $query->where(fn ($w) => $w->where('nama', 'like', "%{$q}%")->orWhere('nip', 'like', "%{$q}%"));
        }

        return response()->json($query->paginate(25));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nip' => ['nullable', 'string', 'max:30', 'unique:guru,nip'],
            'status_kepegawaian' => ['nullable', Rule::in(['ASN', 'GTT'])],
            'nama' => ['required', 'string', 'max:120'],
            'agama' => ['nullable', 'string', 'max:30'],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'alamat' => ['nullable', 'string'],
        ]);

        $guru = Guru::create($data);

        $this->audit->catat('buat_guru', 'guru', $guru->id_guru, null, null, $guru->only(['nip', 'nama']));

        return response()->json($guru, 201);
    }

    public function update(Request $request, Guru $guru): JsonResponse
    {
        $data = $request->validate([
            'nip' => ['sometimes', 'nullable', 'string', 'max:30', Rule::unique('guru', 'nip')->ignore($guru->id_guru, 'id_guru')],
            'status_kepegawaian' => ['sometimes', 'nullable', Rule::in(['ASN', 'GTT'])],
            'nama' => ['sometimes', 'string', 'max:120'],
            'agama' => ['sometimes', 'nullable', 'string', 'max:30'],
            'jenis_kelamin' => ['sometimes', 'nullable', Rule::in(['L', 'P'])],
            'alamat' => ['sometimes', 'nullable', 'string'],
        ]);

        $sebelum = $guru->only(array_keys($data));
        $guru->update($data);

        $this->audit->catat('ubah_guru', 'guru', $guru->id_guru, null, $sebelum, $guru->only(array_keys($data)));

        return response()->json($guru);
    }
}
