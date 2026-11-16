<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class SekolahRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'npsn' => ['nullable', 'digits:8'],
            'nama' => ['required', 'string', 'max:120'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'kota' => ['nullable', 'string', 'max:60'],
            'telepon' => ['nullable', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:100'],
            'kepala_sekolah' => ['nullable', 'string', 'max:120'],
            'nip_kepala_sekolah' => ['nullable', 'string', 'max:30'],
        ];
    }
}
