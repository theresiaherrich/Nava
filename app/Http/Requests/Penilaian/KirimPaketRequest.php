<?php

namespace App\Http\Requests\Penilaian;

use App\Services\PenilaianAccess;
use Illuminate\Foundation\Http\FormRequest;

class KirimPaketRequest extends FormRequest
{
    public function authorize(): bool
    {
        PenilaianAccess::pastikanPengampu($this->route('rombel'));

        return true;
    }

    public function rules(): array
    {
        return [
            'id_periode' => ['required', 'integer', 'exists:periode_nilai,id_periode'],
            'id_kelas' => ['required', 'integer', 'exists:kelas,id_kelas'],
            'konfirmasi_belum_lengkap' => ['sometimes', 'boolean'],
        ];
    }
}
