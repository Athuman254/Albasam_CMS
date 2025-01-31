<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function rules(): array
    {
        if($this->isMethod('POST')) {
            return $this->createRules();
        }
        return $this->updateRules();
    }

    public function createRules(): array
    {
        return [
            'name' => ['required', 'min:3', Rule::unique('roles', 'name')],
            'description' => ['nullable'],
            'permissions' => ['required', 'array'],
//            'permissions.*.id' => ['required', Rule::exists('permissions', 'id')],
        ];
    }

    public function updateRules(): array
    {
        return [
            'name' => ['required', 'min:3'],
            'description' => ['nullable'],
            'permissions' => ['required', 'array'],
//            'permissions.*.id' => ['required', Rule::exists('permissions', 'id')],
        ];
    }
}
