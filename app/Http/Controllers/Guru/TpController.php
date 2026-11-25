<?php

namespace App\Http\Controllers\Guru;

use App\Exceptions\AturanBisnisException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Penilaian\SalinTpRequest;
use App\Http\Requests\Penilaian\TpRequest;
use App\Http\Requests\Penilaian\UrutanTpRequest;
use App\Models\NilaiTp;
use App\Models\Rombel;
use App\Models\Tp;
use App\Services\PenilaianAccess;
use App\Services\TpService;
use Illuminate\Http\JsonResponse;

/** Kelola TP oleh guru pengampu. */
class TpController extends Controller
{
    public function __construct(private TpService $tp) {}

    public function index(Rombel $rombel): JsonResponse
    {
        PenilaianAccess::pastikanPengampu($rombel);
        $tp = $rombel->tp()->get();
        $terisi = NilaiTp::whereIn('id_tp', $tp->pluck('id_tp'))->selectRaw('id_tp, count(*) as n')->groupBy('id_tp')->pluck('n', 'id_tp');

        return $this->ok([
            'maks' => Tp::MAKS_PER_ROMBEL,
            'terkunci' => $this->tp->strukturTerkunci($rombel),
            'tp' => $tp->map(fn (Tp $t) => $t->only(['id_tp', 'urutan', 'kode', 'deskripsi', 'hanya_sts']) + [
                'kktp' => $t->kktpEfektif($rombel->mapel),
                'jumlah_nilai' => (int) ($terisi[$t->id_tp] ?? 0),
                'dapat_dihapus' => ! isset($terisi[$t->id_tp]),
                'tp_sas' => $t->id_tp === $tp->last()->id_tp,
            ])->values(),
        ]);
    }

    public function store(TpRequest $request, Rombel $rombel): JsonResponse
    {
        PenilaianAccess::pastikanPengampu($rombel);

        return $this->created($this->tp->tambah($rombel, $request->validated()), 'TP ditambahkan.');
    }

    public function update(TpRequest $request, Tp $tp): JsonResponse
    {
        PenilaianAccess::pastikanPengampu($tp->rombel);

        return $this->ok($this->tp->ubah($tp, $request->validated()), 'TP diperbarui.');
    }

    public function destroy(Tp $tp): JsonResponse
    {
        PenilaianAccess::pastikanPengampu($tp->rombel);
        $this->tp->hapus($tp);

        return $this->ok(null, 'TP dihapus.');
    }

    public function urutan(UrutanTpRequest $request, Rombel $rombel): JsonResponse
    {
        PenilaianAccess::pastikanPengampu($rombel);
        $this->tp->urutkan($rombel, array_map('intval', $request->input('id_tp')));

        return $this->ok($rombel->tp()->get(['id_tp', 'urutan', 'deskripsi']), 'Urutan TP disimpan.');
    }

    public function salin(SalinTpRequest $request, Rombel $rombel): JsonResponse
    {
        PenilaianAccess::pastikanPengampu($rombel);
        $idTp = array_map('intval', $request->input('id_tp'));
        if (Tp::whereKey($idTp)->where('id_rombel', '!=', $rombel->id_rombel)->exists()) {
            throw new AturanBisnisException('TP sumber harus berasal dari rombel ini.', 'VALIDATION_ERROR', 422, ['id_tp' => ['Bukan TP rombel ini.']]);
        }

        $hasil = [];
        foreach (Rombel::whereKey($request->input('id_rombel_tujuan'))->get() as $tujuan) {
            PenilaianAccess::pastikanPengampu($tujuan);
            $hasil[$tujuan->id_rombel] = count($this->tp->salin($tujuan, $idTp));
        }

        return $this->ok(['disalin' => $hasil], 'TP disalin sebagai baris baru.');
    }
}
