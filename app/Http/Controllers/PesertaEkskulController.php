<?php

namespace App\Http\Controllers;

use App\Models\PesertaEkskul;
use App\Models\Predikat;
use App\Models\SiswaKelas;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Bagian 4.7: peserta dan predikat ekskul per siswa per semester (kesiswaan dan admin). */
class PesertaEkskulController extends Controller
{
    /**
     * PUT /peserta-ekskul
     * body: semester_id, ekskul_id, items[{siswa_id, predikat: SB|B|K, catatan?}]
     * Menambah peserta baru atau memperbarui predikat peserta yang sudah ada (unik: ekskul + siswa + semester).
     */
    public function simpan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'semester_id' => ['required', 'integer', 'exists:semester,id'],
            'ekskul_id' => ['required', 'integer', 'exists:ekskul,id_ekskul'],
            'items' => ['required', 'array', 'min:1', 'max:500'],
            'items.*.siswa_id' => ['required', 'integer', 'distinct', 'exists:siswa,id'],
            'items.*.predikat' => ['required', 'in:SB,B,K'],
            'items.*.catatan' => ['nullable', 'string', 'max:1000'],
        ]);
        $user = $request->user();

        $hasil = DB::transaction(function () use ($data, $user) {
            $terdaftar = SiswaKelas::where('semester_id', $data['semester_id'])->pluck('siswa_id');
            $asing = collect($data['items'])->pluck('siswa_id')->diff($terdaftar);
            if ($asing->isNotEmpty()) {
                throw ValidationException::withMessages(['items' => 'Siswa berikut belum terdaftar di kelas pada semester ini: '.$asing->implode(', ').'.']);
            }

            $predikat = Predikat::where('aktif', true)->pluck('id_predikat', 'predikat');
            $hilang = collect($data['items'])->pluck('predikat')->unique()->reject(fn ($kode) => $predikat->has($kode));
            if ($hilang->isNotEmpty()) {
                throw ValidationException::withMessages(['items' => 'Master predikat belum tersedia untuk: '.$hilang->implode(', ').'. Jalankan PredikatSeeder.']);
            }

            $baru = 0;
            $diperbarui = 0;
            $baris = [];
            foreach ($data['items'] as $item) {
                $peserta = PesertaEkskul::updateOrCreate(
                    ['id_ekskul' => $data['ekskul_id'], 'id_siswa' => $item['siswa_id'], 'id_semester' => $data['semester_id']],
                    ['id_predikat' => $predikat[$item['predikat']], 'catatan' => $item['catatan'] ?? null, 'diisi_oleh' => $user->id],
                );
                $peserta->wasRecentlyCreated ? $baru++ : $diperbarui++;
                $baris[] = ['id' => $peserta->id_peserta_ekskul, 'siswa_id' => (int) $item['siswa_id'], 'predikat' => $item['predikat'], 'catatan' => $peserta->catatan];
            }
            AuditService::record('simpan_peserta_ekskul', 'peserta_ekskul', (int) $data['ekskul_id'], null, ['semester_id' => $data['semester_id'], 'dibuat' => $baru, 'diperbarui' => $diperbarui], null, $user->id);

            return ['dibuat' => $baru, 'diperbarui' => $diperbarui, 'peserta' => $baris];
        });

        return response()->json(['data' => $hasil + ['ekskul_id' => (int) $data['ekskul_id'], 'semester_id' => (int) $data['semester_id']]]);
    }
}
