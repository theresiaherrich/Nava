<?php

namespace App\Http\Controllers\Akun;

use App\Exports\KredensialExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Akun\BuatMassalRequest;
use App\Services\AccountService;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class AkunMassalController extends Controller
{
    public function __construct(private AccountService $akun) {}

    /** Kredensial awal dikembalikan SEKALI (JSON atau unduhan xlsx) dan tidak disimpan. */
    public function store(BuatMassalRequest $request): Response
    {
        $hasil = $this->akun->buatMassal($request->string('jenis'), $request->input('ids'));

        if ($request->input('format') === 'xlsx') {
            return Excel::download(new KredensialExport($hasil), 'kredensial-'.$request->string('jenis').'-'.now()->format('Ymd-His').'.xlsx');
        }

        return $this->created(['jumlah' => count($hasil), 'kredensial' => $hasil],
            count($hasil) === 0 ? 'Tidak ada profil tanpa akun.' : 'Akun dibuat. Simpan kredensial ini sekarang; tidak akan ditampilkan lagi.');
    }
}
