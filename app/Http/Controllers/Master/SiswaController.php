<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\OrangTua;
use App\Models\Siswa;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SiswaController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request): JsonResponse
    {
        $query = Siswa::query()->orderBy('nama');

        if ($q = trim((string) $request->query('q', ''))) {
            $query->where(fn ($w) => $w->where('nama', 'like', "%{$q}%")
                ->orWhere('nis', 'like', "%{$q}%")
                ->orWhere('nisn', 'like', "%{$q}%"));
        }

        return response()->json($query->paginate(25));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nis' => ['required', 'string', 'max:30', 'unique:siswa,nis'],
            'nisn' => ['nullable', 'string', 'max:30', 'unique:siswa,nisn'],
            'nama' => ['required', 'string', 'max:120'],
            'agama' => ['nullable', 'string', 'max:30'],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'tahun_masuk' => ['nullable', 'integer', 'digits:4'],
            'urutan_masuk' => ['nullable', 'integer', 'min:1'],
            'alamat' => ['nullable', 'string'],
        ]);

        $siswa = Siswa::create($data);

        $this->audit->catat('buat_siswa', 'siswa', $siswa->id_siswa, null, null, $siswa->only(['nis', 'nama']));

        return response()->json($siswa, 201);
    }

    public function update(Request $request, Siswa $siswa): JsonResponse
    {
        $data = $request->validate([
            'nis' => ['sometimes', 'string', 'max:30', Rule::unique('siswa', 'nis')->ignore($siswa->id_siswa, 'id_siswa')],
            'nisn' => ['sometimes', 'nullable', 'string', 'max:30', Rule::unique('siswa', 'nisn')->ignore($siswa->id_siswa, 'id_siswa')],
            'nama' => ['sometimes', 'string', 'max:120'],
            'agama' => ['sometimes', 'nullable', 'string', 'max:30'],
            'jenis_kelamin' => ['sometimes', 'nullable', Rule::in(['L', 'P'])],
            'tahun_masuk' => ['sometimes', 'nullable', 'integer', 'digits:4'],
            'urutan_masuk' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'alamat' => ['sometimes', 'nullable', 'string'],
        ]);

        $sebelum = $siswa->only(array_keys($data));
        $siswa->update($data);

        $this->audit->catat('ubah_siswa', 'siswa', $siswa->id_siswa, null, $sebelum, $siswa->only(array_keys($data)));

        return response()->json($siswa);
    }

    /**
     * Relasi orang tua-anak. Admin menghubungkan akun berperan ORANG_TUA ke siswa
     * dan langsung memverifikasinya. Pendaftaran orang tua mandiri adalah ranah A4.
     */
    public function hubungkanOrangTua(Request $request, Siswa $siswa): JsonResponse
    {
        $data = $request->validate([
            'hubungan' => ['required', Rule::in(['Ayah', 'Ibu', 'Wali'])],
            'user_id' => ['required', 'integer', 'exists:user,id_user'],
            'nama' => ['nullable', 'string', 'max:120'],
        ]);

        $user = User::findOrFail($data['user_id']);

        if (! $user->hasRole('ORANG_TUA')) {
            throw ValidationException::withMessages(['user_id' => 'Akun harus memiliki peran Orang Tua.']);
        }

        $cari = fn () => OrangTua::where('id_siswa', $siswa->id_siswa)->where('hubungan', $data['hubungan']);

        $payload = [
            'id_user' => $user->id_user,
            'nama' => $data['nama'] ?? $user->nama,
            'status_verifikasi' => 'verified',
            'diverifikasi_oleh' => Auth::id(),
            'waktu_verifikasi' => now(),
        ];

        DB::transaction(function () use ($siswa, $data, $payload, $cari) {
            $sebelum = $cari()->first()?->only(['id_user', 'status_verifikasi']);

            if ($sebelum !== null) {
                $cari()->update($payload);
            } else {
                OrangTua::create(['id_siswa' => $siswa->id_siswa, 'hubungan' => $data['hubungan']] + $payload);
            }

            $this->audit->catat('hubungkan_orang_tua', 'siswa', $siswa->id_siswa, null, $sebelum, [
                'hubungan' => $data['hubungan'],
                'id_user' => $data['user_id'],
            ]);
        });

        return response()->json(['message' => 'Orang tua terhubung ke siswa.']);
    }
}
