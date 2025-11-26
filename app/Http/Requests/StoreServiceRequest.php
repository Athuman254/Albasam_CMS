<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
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
        return [
            'title' => 'required',
            'discription' => 'required',
            'content' => 'required',
            'featured_image' => 'required',
            'service_includes' => 'nullable',
            'price' => 'nullable',
            'vedio_src' => 'nullable',
            'category_id' => 'nullable',
            'status' => 'required',
            'has_started' => 'nullable',
            'starts_at' => 'nullable',

        ];
    }
}
