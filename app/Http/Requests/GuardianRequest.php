<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardianRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
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
            'student_id' => ['required', Rule::exists('students', 'id')],
            'relationship_id' => ['nullable', Rule::exists('relationships', 'id')],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', Rule::unique('guardians', 'email')],
            'phone' => ['nullable', 'string', Rule::unique('guardians', 'phone')],
            'identification_number' => ['nullable', 'string', Rule::unique('guardians', 'identification_number')],
            'profession' => ['nullable', 'string'],
        ];
    }
    
    public function updateRules(): array
    {
        return [
            'student_id' => ['required', Rule::exists('students', 'id')],
            'relationship_id' => ['nullable', Rule::exists('relationships', 'id')],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', Rule::unique('guardians', 'email')->ignore($this->guardian)],
            'phone' => ['nullable', 'string', Rule::unique('guardians', 'phone')->ignore($this->guardian)],
            'identification_number' => ['nullable', 'string', Rule::unique('guardians', 'identification_number')->ignore($this->guardian)],
            'profession' => ['nullable', 'string'],
        ];
    }
    
    protected function prepareForValidation(): void
    {
        $this->merge(
            collect($this->all())
                ->map(fn($value) => $value === '' ? null : $value)
                ->toArray()
        );
    }
}
