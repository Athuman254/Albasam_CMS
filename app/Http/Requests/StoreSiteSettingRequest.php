<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiteSettingRequest extends FormRequest
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
            "id"=> "nullable",
            "template_name"=> "required",
            "color"=> "nullable",
            "secondary_color"=> "nullable",
            "font_family"=> "nullable",
            "background_image"=> "nullable",
            "banner_text"=> "nullable",
            "meta_title"=>  "nullable",
            "meta_description"=> "nullable",
            "meta_keywords"=> "nullable",
            "is_active"=> "nullable",
        ];
    }
}
