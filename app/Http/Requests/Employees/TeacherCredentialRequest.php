<?php

namespace App\Http\Requests\Employees;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherCredentialRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'use_existing_user' => ['boolean'],
            'teacher_id' => [Rule::exists('teachers', 'id')],
            'employee_id' => [Rule::exists('employees', 'id')],
            'user_id' => [
                'nullable',
                Rule::requiredIf(fn () => $this->input('use_existing_user') === true),
                Rule::exists('users', 'id')
            ],
            'name' => [
                'nullable',
                Rule::requiredIf(fn () => $this->input('use_existing_user') === false),
                Rule::unique('users', 'name')->ignore($this->user)
            ],
            'username' => [
                'nullable',
                Rule::requiredIf(fn () => $this->input('use_existing_user') === false),
                Rule::unique('users', 'username')->ignore($this->user)
            ],
            'email' => [
                'nullable',
                Rule::requiredIf(fn () => $this->input('use_existing_user') === false),
                'email',
                Rule::unique('users', 'email')->ignore($this->user)
            ],
            'phone' => [
                'nullable',
                Rule::requiredIf(fn () => $this->input('use_existing_user') === false),
                Rule::unique('users', 'phone')->ignore($this->user)
            ],
            'password' => [
                'nullable',
                Rule::requiredIf(fn () => $this->input('use_existing_user') === false)
            ],
            'activated' => ['boolean'],
            'is_teacher' => ['boolean']
        ];
    }
}
