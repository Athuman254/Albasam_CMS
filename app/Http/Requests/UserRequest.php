<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function rules(): array
    {
        if($this->isMethod('POST')){
            return $this->storeRules();
        }
        
        return $this->updateRules();
    }
    
    public function storeRules(): array
    {
        return [
            'name' => ['required', Rule::unique('users', 'name')],
            'username' => ['required', Rule::unique('users', 'username')],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'phone' => ['required', Rule::unique('users', 'phone')],
            'role_id' => ['nullable', Rule::exists('roles', 'id')],
            'password' => ['required', 'min:8', 'max:15'],
            'activated' => ['boolean'],
        ];
    }
    
    public function updateRules(): array
    {
        return [
            'name' => ['required', Rule::unique('users', 'name')->ignore($this->user)],
            'username' => ['required', Rule::unique('users', 'username')->ignore($this->user)],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user)],
            'phone' => ['required', Rule::unique('users', 'phone')->ignore($this->user)],
            'role_id' => ['nullable', Rule::exists('roles', 'id')],
            'password' => ['nullable', 'min:8', 'max:15'],
            'activated' => ['boolean'],
        ];
    }
}
