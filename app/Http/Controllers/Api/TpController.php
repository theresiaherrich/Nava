<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AcademicAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TpController extends Controller
{
    // GET daftar TP
    public  function index(int $rombel) {
        AcademicAccess::canReadRombel($rombel);
        $data = DB::table('tp')
            ->where('id_rombel', $rombel)
            ->orderBy('urutan', 'asc')
            ->get();

        return response()->json([
            'data' => $data
        ], 200);
    }

    // POST tambah TP
    public function store(Request $request, int $rombel) {
        AcademicAccess::canTeach($rombel);
        AcademicAccess::editable($rombel);

        $data = $request->validate([
            'kode'=> 'nullable|string|max:30',
            'deskripsi' => 'required|string',
            'masuk_sts' => 'sometimes|boolean',
            'is_sas' => 'sometimes|boolean',
            'bobot' => 'sometimes|nullable|numeric|min:0|max:100',
        ]);

        $idTp = DB::transaction(function () use ($rombel, $data) {
            $rombelAda = DB::table('rombel')
                ->where('id_rombel', $rombel)
                ->lockForUpdate()
                ->first();

            abort_unless($rombelAda, 404, 'Rombel tidak ditemukan');

            $jumlahTp = DB::table('tp')
                ->where('id_rombel', $rombel)
                ->count();

            if ($jumlahTp >= 10) {
                throw ValidationException::withMessages([
                    'message' => 'Jumlah maksimal TP per rombel adalah 10.'
                ]);
            }

            return DB::table('tp')->insertGetId([
            'id_rombel' => $rombel,
            'kode' => $data['kode'] ?? null,
            'deskripsi' => $data['deskripsi'],
            'urutan' => $jumlahTp + 1,
            'masuk_sts' => $data['masuk_sts'] ?? false,
            'is_sas' => $data['is_sas'] ?? false,
            'bobot' => $data['bobot'] ?? null,
            ], 'id_tp');
        });

        return response()->json([
            'message' => 'TP berhasil ditambahkan',
            'id_tp' => $idTp,
        ], 201);
    }
    
    // PUT edit TP
    public function update(Request $request, int $tp)
    {
        $tpLama = DB::table('tp')
            ->where('id_tp', $tp)
            ->first();

        abort_unless($tpLama, 404, 'TP tidak ditemukan.');

        AcademicAccess::canTeach($tpLama->id_rombel);
        AcademicAccess::editable($tpLama->id_rombel);

        $data = $request->validate([
            'kode' => 'sometimes|nullable|string|max:30',
            'deskripsi' => 'sometimes|required|string',
            'masuk_sts' => 'sometimes|boolean',
            'is_sas' => 'sometimes|boolean',
            'bobot' => 'sometimes|nullable|numeric|min:0|max:100',
            // 'urutan' => 'sometimes|integer|min:1|max:10',
        ]);

        DB::table('tp')
            ->where('id_tp', $tp)
            ->update($data);

        return response()->json([
            'message' => 'TP berhasil diperbarui'
        ], 200);
    }

    // DELETE hapus TP
    public function destroy(int $tp)
    {
        $dataTp = DB::table('tp')
            ->where('id_tp', $tp)
            ->first();

        abort_unless($dataTp, 404, 'TP tidak ditemukan.');

        AcademicAccess::canTeach($dataTp->id_rombel);
        AcademicAccess::editable($dataTp->id_rombel);

        DB::transaction(function () use ($tp, $dataTp) {

            // Kunci rombel agar perubahan TP tidak bertabrakan
            DB::table('rombel')
                ->where('id_rombel', $dataTp->id_rombel)
                ->lockForUpdate()
                ->first();

            // Pastikan TP belum digunakan dalam penilaian
            $sudahDinilai = DB::table('nilai_tp')
                ->where('id_tp', $tp)
                ->exists();

            $punyaKktp = DB::table('kktp')
                ->where('id_tp', $tp)
                ->exists();

            abort_if(
                $sudahDinilai || $punyaKktp,
                409,
                'TP sudah digunakan dan tidak boleh dihapus.'
            );

            // Hapus TP yang dipilih
            DB::table('tp')
                ->where('id_tp', $tp)
                ->delete();

            // Ambil seluruh TP tersisa berdasarkan urutan
            $tpTersisa = DB::table('tp')
                ->where('id_rombel', $dataTp->id_rombel)
                ->orderBy('urutan')
                ->orderBy('id_tp')
                ->get();

            // Susun ulang nomor urutan mulai dari 1
            foreach ($tpTersisa as $index => $item) {
                DB::table('tp')
                    ->where('id_tp', $item->id_tp)
                    ->update([
                        'urutan' => $index + 1
                    ]);
            }
        });

        return response()->json([
            'message' => 'TP berhasil dihapus dan urutan diperbarui'
        ], 200);
    }

    // POST salin TP
    public function copy(Request $request)
    {
        $data = $request->validate([
            'rombel_asal' => 'required|integer|exists:rombel,id_rombel',
            'rombel_tujuan' => 'required|integer|different:rombel_asal|exists:rombel,id_rombel',
        ]);

        $asalId = $data['rombel_asal'];
        $tujuanId = $data['rombel_tujuan'];

        AcademicAccess::canTeach($asalId);
        AcademicAccess::canTeach($tujuanId);
        AcademicAccess::editable($tujuanId);

        $asal = DB::table('rombel')->where('id_rombel', $asalId)->first();
        $tujuan = DB::table('rombel')->where('id_rombel', $tujuanId)->first();

        abort_unless(
            $asal->id_mapel == $tujuan->id_mapel &&
            $asal->id_semester == $tujuan->id_semester,
            422,
            'Mapel dan semester harus sama.'
        );

        $jumlahDisalin = DB::transaction(function () use ($asalId, $tujuanId) {
            DB::table('rombel')
                ->where('id_rombel', $tujuanId)
                ->lockForUpdate()
                ->first();

            $tpAsal = DB::table('tp')
                ->where('id_rombel', $asalId)
                ->orderBy('urutan')
                ->get();
                if ($tpAsal->isEmpty()) {
                    throw ValidationException::withMessages([
                        'tp' => 'Rombel asal belum memiliki TP untuk disalin.'
                    ]);
                }

            $jumlahTujuan = DB::table('tp')
                ->where('id_rombel', $tujuanId)
                ->count();

            if ($jumlahTujuan + $tpAsal->count() > 10) {
                throw ValidationException::withMessages([
                    'tp' => 'Hasil salinan melebihi batas 10 TP.'
                ]);
            }

            foreach ($tpAsal as $index => $item) {
                DB::table('tp')->insert([
                    'id_rombel' => $tujuanId,
                    'kode' => $item->kode,
                    'deskripsi' => $item->deskripsi,
                    'urutan' => $jumlahTujuan + $index + 1,
                    'masuk_sts' => $item->masuk_sts,
                    'is_sas' => $item->is_sas,
                    'bobot' => $item->bobot,
                ]);
            }

            return $tpAsal->count();
        });

        return response()->json([
            'message' => 'TP berhasil disalin',
            'jumlah' => $jumlahDisalin
        ], 201);
    }
}