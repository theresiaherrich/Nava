<?php

namespace App\Http\Requests\Kurikulum;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RombelRequest extends FormRequest
{
    public function rules(): array
    {
        $r = $this->route('rombel');
        $semester = $this->input('id_semester', $r?->id_semester);
        $mapel = $this->input('id_mapel', $r?->id_mapel);
        $baru = $this->isMethod('post');

        return [
            'id_semester' => [$baru ? 'required' : 'prohibited', 'integer', 'exists:semester,id_semester'],
            'id_mapel' => [$baru ? 'required' : 'prohibited', 'integer', Rule::exists('mapel', 'id_mapel')->where('aktif', true)],
            'nama' => [$baru ? 'required' : 'sometimes', 'string', 'max:100',
                Rule::unique('rombel', 'nama')->where('id_semester', $semester)->where('id_mapel', $mapel)->ignore($r?->id_rombel, 'id_rombel')],
            'id_guru' => ['sometimes', 'array'],
            'id_guru.*' => ['integer', 'distinct', Rule::exists('guru', 'id_guru')->where('aktif', true)],
        ];
    }
}
