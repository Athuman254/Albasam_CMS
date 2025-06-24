<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CareerRequest extends FormRequest
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
            'title' => ['required', 'string', Rule::unique('careers', 'title')],
            'employment_type_id' => ['nullable', Rule::exists('employment_types', 'id')],
            'location' => ['required', 'string'],
//            'start_date' => ['required', 'date'],
            'deadline_date' => ['required', 'date'],
            'job_description' => ['required', 'string'],
            'active' => ['boolean'],
        ];
    }
    
    public function updateRules(): array
    {
        return [
            'title' => ['required', 'string', Rule::unique('careers', 'title')->ignore($this->career)],
            'slug' => ['nullable', 'string', Rule::unique('blogs', 'slug')->ignore($this->career)],
            'employment_type_id' => ['nullable', Rule::exists('employment_types', 'id')],
            'location' => ['required', 'string'],
//            'start_date' => ['required', 'date'],
            'deadline_date' => ['required', 'date'],
            'job_description' => ['required', 'string'],
            'active' => ['boolean'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'title.required' => 'Vacancy title is required.',
            'title.string' => 'Vacancy title must be a string.',
            'title.unique' => 'Vacancy title already exists.',
        ];
    }
    
    public function prepareForValidation(): void
    {
        $this->merge([
            collect($this->all())
                ->map(fn($value) => $value === '' ? null : $value)
                ->toArray(),
            'title' => ucwords(strtolower($this->input('title'))),
        ]);
    }
}
