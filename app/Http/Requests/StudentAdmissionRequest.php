<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentAdmissionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'registration_details.division_id' => ['required', Rule::exists('divisions', 'id')],
            'registration_details.registered_at' => ['nullable', 'date'],
            'student.first_name' => ['required', 'string', 'max:255'],
            'student.middle_name' => ['nullable', 'string', 'max:255'],
            'student.last_name' => ['required', 'string', 'max:255'],
            'student.admission_number' => ['required', 'string', 'max:255'],
            'student.assessment_number' => ['nullable', 'string', 'max:255'],
            'student.rank_id' => ['required', Rule::exists('ranks', 'id')],
            'student.date_of_birth' => ['nullable', 'date', 'max:255'],
            'student.birth_certificate_number' => ['nullable', 'string', 'max:255'],
            'student.gender_id' => ['required', Rule::exists('genders', 'id')],
            'student.religion_id' => ['required', Rule::exists('religions', 'id')],
            'student.citizenship' => ['nullable', 'string', 'max:255'],
            'student.county' => ['nullable', 'string', 'max:255'],
            'student.ward' => ['nullable', 'string', 'max:255'],
            'student.permanent_address' => ['nullable', 'string', 'max:255'],
            'student.kcpe_score' => ['nullable', 'string', 'max:255'],
            'student.photo' => ['nullable'], // Base64 or File
            'student.scholarship_type' => ['nullable', 'string', Rule::in(['none', 'full', 'half', 'custom'])],
            'student.scholarship_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'student.previous_school' => ['nullable', 'string', 'max:255'],
            'student.specialization' => ['nullable', 'string', 'max:255'],
            'guardians' => ['required', 'array'], // Ensure at least one guardian is provided
            'guardians.*.relationship_id' => ['nullable', Rule::exists('relationships', 'id')],
            'guardians.*.first_name' => ['nullable', 'string', 'max:255'],
            'guardians.*.middle_name' => ['nullable', 'string', 'max:255'],
            'guardians.*.place_of_work' => ['nullable', 'string', 'max:255'],
            'guardians.*.last_name' => ['nullable', 'string', 'max:255'],
            'guardians.*.email' => ['nullable', 'email'],
            'guardians.*.phone' => ['nullable', 'string'],
            'guardians.*.identification_number' => ['nullable', 'string'],
            'guardians.*.profession' => ['nullable', 'string'],
            'other_details.siblings' => ['nullable', 'array'],
            'other_details.siblings.*.name' => ['nullable', 'string', 'max:255'],
            'other_details.siblings.*.age' => ['nullable', 'numeric'],
            'other_details.siblings.*.gender_id' => ['nullable', Rule::exists('genders', 'id')],
            'other_details.siblings.*.current_school' => ['nullable', 'string', 'max:255'],
            'other_details.siblings.*.current_class' => ['nullable', 'string', 'max:255'],
            'other_details.physical_disability' => ['nullable', 'string'],
            'other_details.hobby' => ['nullable'],
            'other_details.medical_details' => ['nullable'],
            'other_details.character_book' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'registration_details.admission_number' => 'You must provide an admission number.',
            'registration_details.division_id' => 'Please select a division.',
            'registration_details.rank_id' => 'Please select a class.',
            'guardians.required' => 'Please provide at least one guardian.',
            'guardians.min' => 'You must add at least one guardian.',
            'guardians.*.first_name.required' => 'The first name of each guardian is required.',
            'guardians.*.first_name.string' => 'The first name must be a valid string.',
            'guardians.*.first_name.max' => 'The first name cannot be longer than 255 characters.',
            'guardians.*.last_name.required' => 'The last name of each guardian is required.',
            'guardians.*.last_name.string' => 'The last name must be a valid string.',
            'guardians.*.last_name.max' => 'The last name cannot be longer than 255 characters.',
            'guardians.*.email.required' => 'The guardian\'s email is required.',
            'guardians.*.email.email' => 'The guardian\'s email must be a valid email address.',
            'guardians.*.phone.required' => 'The guardian\'s phone number is required.',
            'guardians.*.phone.string' => 'The phone number must be a valid string.',
            'guardians.*.identification_number.string' => 'The identification number must be a valid string.',
            'guardians.*.profession.string' => 'The guardian\'s profession must be a valid string.',
            'other_details.siblings.*.name.required_with' => 'Each sibling must have a name if siblings are provided.',
            'other_details.siblings.*.age.required_with' => 'Each sibling must have an age if siblings are provided.',
            'other_details.siblings.*.current_school.required_with' => 'Each sibling must have a current school if siblings are provided.',
            'other_details.siblings.*.current_class.required_with' => 'Each sibling must have a current class if siblings are provided.',
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
