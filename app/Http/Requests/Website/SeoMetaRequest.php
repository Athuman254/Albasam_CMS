<?php

namespace App\Http\Requests\Website;

use Illuminate\Foundation\Http\FormRequest;

class SeoMetaRequest extends FormRequest
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
        if($this->method() == 'POST') {
            return $this->createRules();
        }
        
        return $this->updateRules();
    }
    
    public function createRules(): array
    {
        return [
            'title' => ['required'],
            'description' => ['required'],
            'keywords' => ['required'],
            'seo_able_type' => ['required'],
            'seo_able_id' => ['required'],
        ];
    }
    
    public function updateRules(): array
    {
        return [
            'title' => ['required'],
            'description' => ['required'],
            'keywords' => ['required'],
            'seo_able_type' => ['required'],
            'seo_able_id' => ['required'],
        ];
    }
}
