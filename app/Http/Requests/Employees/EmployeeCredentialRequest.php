<?php

namespace App\Http\Requests\Employees;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeCredentialRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'has_system_access' => ['required', 'boolean'],
            'password' => ['required', 'min:8'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 8 characters.',
        ];
    }
}
