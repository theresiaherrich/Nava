<?php

namespace App\Http\Requests\Master;

use App\Models\Kelas;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KelasRequest extends FormRequest
{
    public function rules(): array
    {
        $kelas = $this->route('kelas');
        $ta = $this->input('id_tahun_ajaran', $kelas?->id_tahun_ajaran);
        $wajib = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'id_tahun_ajaran' => [$this->isMethod('post') ? 'required' : 'prohibited', 'integer', 'exists:tahun_ajaran,id_tahun_ajaran'],
            'nama' => [$wajib, 'string', 'max:50', Rule::unique('kelas', 'nama')->where('id_tahun_ajaran', $ta)->ignore($kelas?->id_kelas, 'id_kelas')],
            'tingkat' => [$wajib, Rule::in(Kelas::TINGKAT)],
        ];
    }
}
