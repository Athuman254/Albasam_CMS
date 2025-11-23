<?php

namespace App\Http\Controllers\FormWizard;

use App\Http\Controllers\Controller;
use App\Models\StudentAdmission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdmissionController extends Controller
{
    public function firstStep(Request $request, StudentAdmission $studentAdmission = null)
    {
        $errorMessages = [
            // REMOVED date validation message
            'registration_details.division_id' => 'Please select a division.',
        ];
        
        $request->validate([
            // REMOVED date validation
            'registration_details.division_id' => ['required', Rule::exists('divisions', 'id')],
        ], $errorMessages);
        
        if($studentAdmission)
        {
            return to_route('admin.admissions.edit', $studentAdmission);
        }
        
        return to_route('admin.admissions.form');
    }
    
    public function secondStep(Request $request, StudentAdmission $studentAdmission = null)
    {
        $errorMessages = [
            'student.first_name.required' => 'The first name is required.',
            'student.first_name.string' => 'The first name must be a valid string.',
            'student.first_name.max' => 'The first name may not be greater than 255 characters.',
            'student.middle_name.string' => 'The middle name must be a valid string.',
            'student.middle_name.max' => 'The middle name may not be greater than 255 characters.',
            'student.last_name.required' => 'The last name is required.',
            'student.last_name.string' => 'The last name must be a valid string.',
            'student.last_name.max' => 'The last name may not be greater than 255 characters.',
            'student.admission_number.required' => 'The admission number is required.',
            'student.admission_number.string' => 'The admission number must be a valid string.',
            'student.admission_number.max' => 'The admission number may not be greater than 255 characters.',
            'student.rank_id.required' => 'Please select a class first.',
            'student.rank_id.exists' => 'The selected class does not exist.',
            'student.gender_id.required' => 'The gender is required.',
            'student.gender_id.exists' => 'The selected gender is invalid.',
            'student.religion_id.required' => 'The religion is required.',
            'student.religion_id.exists' => 'The selected religion is invalid.',
            'student.date_of_birth.required' => 'The date of birth is required.',
            'student.date_of_birth.string' => 'The date of birth must be a valid string.',
            'student.date_of_birth.max' => 'The date of birth may not be greater than 255 characters.',
            'student.birth_certificate_number.string' => 'The birth certificate number must be a valid string.',
            'student.birth_certificate_number.max' => 'The birth certificate number may not be greater than 255 characters.',
            'student.citizenship.required' => 'The citizenship is required.',
            'student.citizenship.string' => 'The citizenship must be a valid string.',
            'student.citizenship.max' => 'The citizenship may not be greater than 255 characters.',
            'student.county.string' => 'The county must be a valid string.',
            'student.county.max' => 'The county may not be greater than 255 characters.',
            'student.ward.string' => 'The ward must be a valid string.',
            'student.ward.max' => 'The ward may not be greater than 255 characters.',
            'student.permanent_address.required' => 'The permanent address is required.',
            'student.permanent_address.string' => 'The permanent address must be a valid string.',
            'student.permanent_address.max' => 'The permanent address may not be greater than 255 characters.',
            'student.kcpe_score.string' => 'The KCPE score must be a valid string.',
            'student.kcpe_score.max' => 'The KCPE score may not be greater than 255 characters.',
            'student.previous_school.string' => 'The previous school must be a valid string.',
            'student.previous_school.max' => 'The previous school may not be greater than 255 characters.',
        ];
        
        $request->validate([
            'student.first_name' => ['required', 'string', 'max:255'],
            'student.middle_name' => ['nullable', 'string', 'max:255'],
            'student.last_name' => ['required', 'string', 'max:255'],
            'student.admission_number' => ['required', 'string', 'max:255'],
            'student.rank_id' => ['required', Rule::exists('ranks', 'id')],
            'student.gender_id' => ['required', Rule::exists('genders', 'id')],
            'student.religion_id' => ['required', Rule::exists('religions', 'id')],
            'student.date_of_birth' => ['nullable', 'string', 'max:255'],
            'student.birth_certificate_number' => ['nullable', 'string', 'max:255'],
            'student.citizenship' => ['nullable', 'string', 'max:255'],
            'student.county' => ['nullable', 'string', 'max:255'],
            'student.ward' => ['nullable', 'string', 'max:255'],
            'student.permanent_address' => ['nullable', 'string', 'max:255'],
            'student.kcpe_score' => ['nullable', 'string', 'max:255'],
            'student.previous_school' => ['nullable', 'string', 'max:255'],
        ], $errorMessages);
        
        if($studentAdmission)
        {
            return to_route('admin.admissions.edit', $studentAdmission);
        }
        
        return to_route('admin.admissions.form');
    }
    
    public function thirdStep(Request $request, StudentAdmission $studentAdmission = null)
    {
        $errorMessages = [
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
        ];
        
        $request->validate([
            'guardians' => ['nullable', 'array'], // Ensure at least one guardian is provided
            'guardians.*.relationship_id' => ['nullable', Rule::exists('relationships', 'id')],
            'guardians.*.first_name' => ['nullable', 'string', 'max:255'],
            'guardians.*.middle_name' => ['nullable', 'string', 'max:255'],
            'guardians.*.last_name' => ['nullable', 'string', 'max:255'],
            'guardians.*.email' => ['nullable', 'email'],
            'guardians.*.phone' => ['nullable', 'string'],
            'guardians.*.identification_number' => ['nullable', 'string'],
            'guardians.*.profession' => ['nullable', 'string'],
        ], $errorMessages);
        
        if($studentAdmission)
        {
            return to_route('admin.admissions.edit', $studentAdmission);
        }
        
        return to_route('admin.admissions.form');
    }
    
    public function otherDetailsValidation(Request $request)
    {
        return $request->validate([
            'other_details.physical_disability' => ['nullable', 'string'],
            'other_details.hobby' => ['nullable', 'string'],
            'other_details.siblings' => ['nullable', 'array'],
            'other_details.siblings.*.name' => ['nullable', 'string', 'max:255'],
            'other_details.siblings.*.age' => ['nullable', 'numeric'],
            'other_details.siblings.*.gender_id' => ['nullable', Rule::exists('genders', 'id')],
            'other_details.siblings.*.current_school' => ['nullable', 'string', 'max:255'],
            'other_details.siblings.*.current_class' => ['nullable', 'string', 'max:255'],
        ]);
    }
}