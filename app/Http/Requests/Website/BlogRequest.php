<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlogRequest extends FormRequest
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
            'title' => ['required', 'string', Rule::unique('blogs', 'title')],
            'blog_category_id' => ['nullable', 'string', Rule::exists('blog_categories', 'id')],
            'details' => ['required', 'string'],
            'active' => ['boolean'],
            'media' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:10240'],
        ];
    }
    
    public function updateRules(): array
    {
        return [
            'title' => ['required', 'string', Rule::unique('blogs', 'title')->ignore($this->blog)],
            'slug' => ['nullable', 'string', Rule::unique('blogs', 'slug')->ignore($this->blog)],
            'blog_category_id' => ['nullable', 'string', Rule::exists('blog_categories', 'id')],
            'details' => ['required', 'string'],
            'active' => ['boolean'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'title.required' => 'Project title is required.',
            'title.string' => 'Project title must be a string.',
            'title.unique' => 'Project title already exists.',
            'media.image' => 'Project media must be a image.',
            'media.mimes' => 'Project media must be a file of type: jpeg, png, jpg.',
            'media.max' => 'Project media must be less than 5MB.',
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
