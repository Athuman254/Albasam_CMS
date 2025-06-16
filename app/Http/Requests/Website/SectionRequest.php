<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SectionRequest extends FormRequest
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
            'page_id' => ['required', Rule::exists('pages', 'id')],
            'type' => ['required', 'string'],
            'title' => ['nullable', 'string'],
            'sub_title' => ['nullable', 'string'],
            'component_type' => ['nullable', 'string'],
            'details' => ['nullable', 'string'],
            'include_contact_cards' => ['boolean'],
            'section_has_image' => ['boolean'],
            'section_image_first' => ['boolean'],
            'media' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5020'],
            'has_cta_buttons' => ['boolean'],
            'cta_buttons' => ['nullable', 'array', 'min:1'],
            'cta_buttons.*.page' => ['nullable'],
            'cta_buttons.*.cta_button_text' => ['nullable', 'string'],
            'cta_buttons.*.cta_button_type' => ['nullable'],
            'map_link' => ['nullable', 'url']
        ];
    }
    
    public function updateRules(): array
    {
        return [
            'page_id' => ['required', Rule::exists('pages', 'id')],
            'type' => ['required', 'string'],
            'title' => ['nullable', 'string'],
            'sub_title' => ['nullable', 'string'],
            'component_type' => ['nullable', 'string'],
            'details' => ['nullable', 'string'],
            'include_contact_cards' => ['boolean'],
            'section_has_image' => ['boolean'],
            'section_image_first' => ['boolean'],
            'has_cta_buttons' => ['boolean'],
            'map_link' => ['nullable', 'url']
        ];
    }
    
    public function messages(): array
    {
        return [
            'page_id.required' => 'The page field is required.',
            'page_id.exists' => 'The selected page does not exist.',
            
            'type.required' => 'The section type is required.',
            'type.string' => 'The section type must be a string.',
            
            'title.required' => 'The section title is required.',
            'title.string' => 'The section title must be a string.',
            
            'sub_title.required' => 'The section subtitle is required.',
            'sub_title.string' => 'The section subtitle must be a string.',
            
            'details.required' => 'The section details are required.',
            'details.string' => 'The section details must be a string.',
            
            'section_image_first.boolean' => 'The section image alignment value must be true or false.',
            
            'media.image' => 'The section image must be an image file.',
            'media.mimes' => 'The section image must be a file of type: jpeg, png, jpg.',
            'media.max' => 'The section image may not be greater than 5MB.',
            
            'has_cta_buttons.boolean' => 'The CTA button toggle must be true or false.',
            
            'cta_buttons.array' => 'CTA buttons must be provided as an array.',
            'cta_buttons.min' => 'You must add at least one CTA button.',
            
            'cta_buttons.*.cta_button_text.string' => 'The CTA button text must be a string.',
        ];
    }
    
    public function prepareForValidation(): void
    {
        $this->merge([
            'title' => ucwords(strtolower($this->input('title'))),
            'sub_title' => ucwords(strtolower($this->input('sub_title'))),
        ]);
    }
}
