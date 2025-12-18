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
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'geofence_radius' => ['nullable', 'integer', 'min:10', 'max:1000'],
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
            'email' => $this->input('email') ? strtolower($this->input('email')) : null,
            'country' => $this->input('country') ? strtoupper($this->input('country')) : null,
            'state' => $this->input('state') ? strtoupper($this->input('state')) : null,
            'city' => $this->input('city') ? strtoupper($this->input('city')) : null,
            // Convert empty strings to null for numeric fields
            'latitude' => $this->input('latitude') !== '' ? $this->input('latitude') : null,
            'longitude' => $this->input('longitude') !== '' ? $this->input('longitude') : null,
            'geofence_radius' => $this->input('geofence_radius') !== '' ? $this->input('geofence_radius') : null,
        ]);
    }
}
