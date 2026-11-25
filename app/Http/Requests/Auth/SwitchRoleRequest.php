<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SwitchRoleRequest extends FormRequest
{
    public function rules(): array
    {
        return ['peran' => ['required', 'string', Rule::in($this->user()->kodeRoles())]];
    }

    public function messages(): array
    {
        return ['peran.in' => 'Anda tidak memiliki peran tersebut.'];
    }
}
