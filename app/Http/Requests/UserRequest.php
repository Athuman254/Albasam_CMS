<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', Rule::unique('users', 'name')->ignore($this->user_id)],
            'username' => ['required', Rule::unique('users', 'username')->ignore($this->user_id)],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user_id)],
            'phone' => ['required', Rule::unique('users', 'phone')->ignore($this->user_id)],
            'role_id' => ['nullable', Rule::exists('roles', 'id')],
            'password' => ['required', 'min:8', 'max:15'],
            'activated' => ['boolean'],
            'is_admin' => ['boolean'],
            'is_teacher' => ['boolean'],
            'is_parent' => ['boolean'],
        ];
    }
}
