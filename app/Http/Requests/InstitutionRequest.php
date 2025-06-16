<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InstitutionRequest extends FormRequest
{
    public function rules(): array
    {
        if ($this->isMethod('POST')) {
            return $this->createRules();
        }

        return $this->updateRules();
    }

    public function createRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('institutions', 'name')],
            'email' => ['required', 'email', 'max:255', Rule::unique('institutions', 'email')],
            'phone' => ['required', 'string'],
            'country' => ['required', 'string'],
            'state' => ['required', 'string'],
            'city' => ['required', 'string'],
            'physical_address' => ['nullable'],
            'postal_address' => ['nullable'],
            'tax_identification_pin' => ['nullable'],
            'mission' => ['nullable'],
            'vision' => ['nullable'],
            'x_profile' => ['nullable', 'url'],
            'fb_profile' => ['nullable'],
            'ig_profile' => ['nullable'],
            'tiktok_profile' => ['nullable'],
            'youtube_profile' => ['nullable'],
        ];
    }

    public function updateRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string'],
            'country' => ['nullable'],
            'state' => ['nullable'],
            'city' => ['nullable'],
            'physical_address' => ['nullable'],
            'postal_address' => ['nullable'],
            'tax_identification_pin' => ['nullable'],
            'mission' => ['nullable'],
            'vision' => ['nullable'],
            'x_profile' => ['nullable', 'url'],
            'fb_profile' => ['nullable'],
            'ig_profile' => ['nullable'],
            'tiktok_profile' => ['nullable'],
            'youtube_profile' => ['nullable'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
//            'name' => strtoupper($this->input('name')),
            'email' => strtolower($this->input('email')),
            'country' => strtoupper($this->input('country')),
            'state' => strtoupper($this->input('state')),
            'city' => strtoupper($this->input('city')),
        ]);
    }
}
