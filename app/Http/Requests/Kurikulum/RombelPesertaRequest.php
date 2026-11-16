<?php

namespace App\Http\Requests\Kurikulum;

use Illuminate\Foundation\Http\FormRequest;

/** Tambah peserta per anggota kelas, atau sekaligus seluruh anggota satu/lebih kelas. */
class RombelPesertaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id_riwayat_kelas' => ['required_without:id_kelas', 'array', 'max:500'],
            'id_riwayat_kelas.*' => ['integer', 'distinct', 'exists:riwayat_kelas,id_riwayat_kelas'],
            'id_kelas' => ['required_without:id_riwayat_kelas', 'array', 'max:20'],
            'id_kelas.*' => ['integer', 'distinct', 'exists:kelas,id_kelas'],
        ];
    }
}
