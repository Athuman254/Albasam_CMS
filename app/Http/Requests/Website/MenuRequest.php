<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuRequest extends FormRequest
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
            'title' => ['required', 'string', Rule::unique('menus', 'title')],
            'type' => ['required', 'string', /* Rule::in(array_keys(Menu::TYPES)) */ ],
            'page_id' => ['nullable', Rule::exists('pages', 'id')],
            'url' => ['nullable', 'string', Rule::unique('menus', 'url')],
            'has_children' => ['boolean'],
            'child_type' => ['nullable', 'string'],
            'children' => ['nullable', 'array'],
            'children.*' => ['nullable', Rule::exists('pages', 'id')],
            'component' => ['nullable', 'string'],
        ];
    }
    
    public function updateRules(): array
    {
        return [
            'title' => ['required', 'string', Rule::unique('menus', 'title')->ignore($this->menu)],
            'type' => ['required', 'string', /* Rule::in(array_keys(Menu::TYPES)) */ ],
            'page_id' => ['nullable', Rule::exists('pages', 'id')],
            'url' => ['nullable', 'string', Rule::unique('menus', 'url')->ignore($this->menu)],
            'order' => ['nullable', 'integer', Rule::unique('menus', 'order')->ignore($this->menu)],
            'has_children' => ['boolean'],
            'child_type' => ['nullable', 'string'],
            'children' => ['nullable', 'array'],
            'children.*' => ['nullable', Rule::exists('pages', 'id')],
            'component' => ['nullable', 'string'],
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
