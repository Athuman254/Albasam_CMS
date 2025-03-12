<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherRequest;
use App\Http\Resources\Resource;
use App\Models\EmergencyContact;
use App\Models\Employee;
use App\Models\Qualification;
use App\Models\Teacher;
use App\Models\WorkHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TeacherController extends Controller
{
    public function dataTable()
    {
        $teachers = QueryBuilder::for(
            Teacher::with(['employee.employment_type', 'employee.employment_status', 'honorific', 'specialization', 'title'])
                ->orderBy('first_name')
        )->allowedFilters([
            AllowedFilter::scope('search', 'Search'),
        ])->jsonPaginate();

        return Resource::collection($teachers);
    }

    public function index()
    {
        return Inertia::render('admin/Employees/Teachers/Index', []);
    }

    public function create()
    {
        return Inertia::render('admin/Employees/Teachers/Create');
    }

    public function store(TeacherRequest $request)
    {
        $validatedData = $request->validated();
        
        DB::beginTransaction();

        try {
            $employee = Employee::create([
                'first_name' => $validatedData['personal_details']['first_name'],
                'middle_name' => $validatedData['personal_details']['middle_name'],
                'last_name' => $validatedData['personal_details']['last_name'],
                'honorific_id' => $validatedData['personal_details']['honorific_id'],
                'marital_status_id' => $validatedData['personal_details']['marital_status_id'],
                'gender_id' => $validatedData['personal_details']['gender_id'],
                'religion_id' => $validatedData['personal_details']['religion_id'],
                'email' => $validatedData['personal_details']['email'],
                'primary_phone' => $validatedData['personal_details']['primary_phone'],
                'secondary_phone' => $validatedData['personal_details']['secondary_phone'],
                'permanent_physical_address' => $validatedData['personal_details']['permanent_physical_address'],
                'secondary_physical_address' => $validatedData['personal_details']['secondary_physical_address'],
                'postal_address' => $validatedData['personal_details']['postal_address'],
                'identification_number' => $validatedData['personal_details']['identification_number'],
                'tax_identification_pin' => $validatedData['personal_details']['tax_identification_pin'],
                'staff_number' => $validatedData['employee_details']['staff_number'],
                'date_of_hire' => $validatedData['employee_details']['date_of_hire'],
                'employment_status_id' => $validatedData['employee_details']['employment_status_id'],
                'employment_type_id' => $validatedData['employee_details']['employment_type_id'],
                'job_title_id' => $validatedData['employee_details']['job_title_id'],
            ]);
            
            Teacher::create([
                'employee_id' => $employee->id,
                'first_name' => $validatedData['personal_details']['first_name'],
                'middle_name' => $validatedData['personal_details']['middle_name'],
                'last_name' => $validatedData['personal_details']['last_name'],
                'honorific_id' => $validatedData['personal_details']['honorific_id'],
                'specialization_area_id' => $validatedData['other_details']['specialization_area_id'],
                'tsc_number' => $validatedData['other_details']['tsc_number'],
                'years_of_experience' => $validatedData['other_details']['years_of_experience'],
            ]);

            if (isset($validatedData['employee_details']['emergency_contacts']) && is_array($validatedData['employee_details']['emergency_contacts'])) {
                $emergencyContacts = collect($validatedData['employee_details']['emergency_contacts'])
                    ->filter(function ($contact) {
                        return isset($contact['relationship_id'], $contact['name']);
                    })
                    ->map(function ($contact) use ($employee) {
                    return [
                        'employee_id' => $employee->id,
                        'name' => $contact['name'],
                        'email' => $contact['email'],
                        'phone' => $contact['phone'],
                        'relationship_id' => $contact['relationship_id'],
                    ];
                })->toArray();

                if(!empty($emergencyContacts)) {
                    EmergencyContact::insert($emergencyContacts);
                }
            }

            if (isset($validatedData['other_details']['qualifications']) && is_array($validatedData['other_details']['qualifications'])) {
//                $this->insertQualifications($validatedData['other_details']['qualifications'], $employee);
                $teacherQualifications = collect($validatedData['other_details']['qualifications'])
                    ->filter(function ($qualification) {
                        return isset($qualification['institution_name']);
                    })
                    ->map(function ($qualification) use ($employee) {
                        return [
                            'employee_id' => $employee->id,
                            'institution_name' => $qualification['institution_name'],
                            'course_name' => $qualification['course_name'],
                            'year_of_completion' => $qualification['year_of_completion'],
                            'qualification_type_id' => $qualification['qualification_type_id'],
                        ];
                    })->toArray();
                
                if (!empty($teacherQualifications)) {
                    
                    Qualification::insert($teacherQualifications);
                }
            }

            if (isset($validatedData['other_details']['work_histories']) && !empty($validated['other_details']['work_histories']) && is_array($validatedData['other_details']['work_histories'])) {
                $workHistories = collect($validatedData['other_details']['work_histories'])
                    ->filter(function ($history) {
                        return isset($history['institution_name']);
                    })
                    ->map(function ($history) use ($employee) {
                    return [
                        'employee_id' => $employee->id,
                        'institution_name' => $history['institution_name'],
                        'start_date' => $history['start_date'],
                        'end_date' => $history['end_date'],
                    ];
                })->toArray();

                if(!empty($workHistories)) {
                    WorkHistory::insert($workHistories);
                }
            }

            DB::commit();
            return to_route('teachers.index');

        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Error: ' . $exception->getMessage());
            report($exception);
            return redirect()->back()->withInput()->withErrors(['message' => 'Failed to save teacher details. Please try again.']);
        }
    }
    
    public function show(Teacher $teacher)
    {
        $teacher->load('specialization');
        $employee = Employee::findOrFail($teacher->employee_id);
        $employee->load('employment_type', 'employment_status', 'job_title', 'honorific', 'marital_status', 'gender', 'religion', 'teacher');
        
        return Inertia::render('admin/Employees/Teachers/Show', [
            'teacher' => $teacher,
            'employee' => $employee,
        ]);
    }

    public function edit(Teacher $teacher)
    {
        $employee = Employee::findOrFail($teacher->employee_id);

        return Inertia::render('admin/Employees/Teachers/Edit', [
            'teacher' => $teacher,
            'employee' => $employee,
        ]);
    }

    public function update(Teacher $teacher, TeacherRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $employee = Employee::findOrFail($teacher->employee_id);
            $employee->update([
                'first_name' => $validated['personal_details']['first_name'],
                'middle_name' => $validated['personal_details']['middle_name'],
                'last_name' => $validated['personal_details']['last_name'],
                'honorific_id' => $validated['personal_details']['honorific_id'],
                'marital_status_id' => $validated['personal_details']['marital_status_id'],
                'gender_id' => $validated['personal_details']['gender_id'],
                'religion_id' => $validated['personal_details']['religion_id'],
                'email' => $validated['personal_details']['email'],
                'primary_phone' => $validated['personal_details']['primary_phone'],
                'secondary_phone' => $validated['personal_details']['secondary_phone'],
                'permanent_physical_address' => $validated['personal_details']['permanent_physical_address'],
                'secondary_physical_address' => $validated['personal_details']['secondary_physical_address'],
                'postal_address' => $validated['personal_details']['postal_address'],
                'identification_number' => $validated['personal_details']['identification_number'],
                'tax_identification_pin' => $validated['personal_details']['tax_identification_pin'],
                'staff_number' => $validated['employee_details']['staff_number'],
                'date_of_hire' => $validated['employee_details']['date_of_hire'],
                'employment_status_id' => $validated['employee_details']['employment_status_id'],
                'employment_type_id' => $validated['employee_details']['employment_type_id'],
                'job_title_id' => $validated['employee_details']['job_title_id'],
            ]);

            $teacher->update([
                'first_name' => $validated['personal_details']['first_name'],
                'middle_name' => $validated['personal_details']['middle_name'],
                'last_name' => $validated['personal_details']['last_name'],
                'honorific_id' => $validated['personal_details']['honorific_id'],
                'specialization_area_id' => $validated['other_details']['specialization_area_id'],
                'teacher_title_id' => $validated['other_details']['teacher_title_id'],
                'tsc_number' => $validated['other_details']['tsc_number'],
                'years_of_experience' => $validated['other_details']['years_of_experience'],
            ]);

            if (isset($request->employee_details['emergency_contacts']) && is_array($request->employee_details['emergency_contacts'])) {
                $employee->contacts()->delete();

                $emergencyContacts = collect($request->employee_details['emergency_contacts'])
                    ->filter(function ($contact) {
                        return isset($contact['relationship_id'], $contact['name']);
                    })
                    ->map(function ($contact) use ($employee) {
                    return [
                        'employee_id' => $employee->id,
                        'name' => $contact['name'],
                        'email' => $contact['email'],
                        'phone' => $contact['phone'],
                        'relationship_id' => $contact['relationship_id'],
                    ];
                })->toArray();

                if ($emergencyContacts) {
                    EmergencyContact::insert($emergencyContacts);
                }
            }

            if (isset($validated['other_details']['qualifications']) && is_array($validated['other_details']['qualifications'])) {
                $employee->qualifications()->delete();
                
                $qualifications = collect($validated['other_details']['qualifications'])
                    ->filter(function ($qualification) {
                        return isset($qualification['institution_name']);
                    })
                    ->map(function ($qualification) use ($employee) {
                        return [
                            'employee_id' => $employee->id,
                            'institution_name' => $qualification['institution_name'],
                            'course_name' => $qualification['course_name'],
                            'year_of_completion' => $qualification['year_of_completion'],
                            'qualification_type_id' => $qualification['qualification_type_id'],
                        ];
                    })->toArray();
                
                if (!empty($qualifications)) {
                    
                    Qualification::insert($qualifications);
                }
            }

            if (!empty($validated['other_details']['work_histories']) && is_array($validated['other_details']['work_histories'])) {

                $employee->histories()->delete();

                $workHistories = collect($validated['other_details']['work_histories'])
                    ->filter(function ($history) {
                        return isset($history['institution_name']);
                    })
                    ->map(function ($history) use ($employee) {
                    return [
                        'employee_id' => $employee->id,
                        'institution_name' => $history['institution_name'],
                        'start_date' => $history['start_date'],
                        'end_date' => $history['end_date'],
                    ];
                })->toArray();

                if(!empty($workHistories)) {
                    WorkHistory::insert($workHistories);
                }
            }

            DB::commit();
            return to_route('teachers.index');

        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Error: ' . $exception->getMessage());
            report($exception);
            return redirect()->back()->withInput()->withErrors(['message' => 'Failed to update teacher details. Please try again.']);
        }
    }

    public function firstStep(Request $request, Employee $employee = null)
    {
        $errorMessages = [
            'personal_details.first_name.required' => 'The first name is required.',
            'personal_details.first_name.string' => 'The first name must be a valid string.',
            'personal_details.first_name.max' => 'The first name must not exceed 255 characters.',
            'personal_details.middle_name.string' => 'The middle name must be a valid string.',
            'personal_details.middle_name.max' => 'The middle name must not exceed 255 characters.',
            'personal_details.last_name.required' => 'The last name is required.',
            'personal_details.last_name.string' => 'The last name must be a valid string.',
            'personal_details.last_name.max' => 'The last name must not exceed 255 characters.',
            'personal_details.honorific_id.exists' => 'The selected honorific is invalid.',
            'personal_details.marital_status_id.required' => 'The marital status is required.',
            'personal_details.marital_status_id.exists' => 'The selected marital status is invalid.',
            'personal_details.gender_id.required' => 'The gender is required.',
            'personal_details.gender_id.exists' => 'The selected gender is invalid.',
            'personal_details.religion_id.required' => 'The religion is required.',
            'personal_details.religion_id.exists' => 'The selected religion is invalid.',
            'personal_details.email.email' => 'The email must be a valid email address.',
            'personal_details.email.max' => 'The email must not exceed 255 characters.',
            'personal_details.primary_phone.required' => 'The primary phone number is required.',
            'personal_details.primary_phone.string' => 'The primary phone number must be a valid string.',
            'personal_details.primary_phone.max' => 'The primary phone number must not exceed 255 characters.',
            'personal_details.secondary_phone.string' => 'The secondary phone number must be a valid string.',
            'personal_details.secondary_phone.max' => 'The secondary phone number must not exceed 255 characters.',
            'personal_details.permanent_physical_address.required' => 'The permanent physical address is required.',
            'personal_details.permanent_physical_address.string' => 'The permanent physical address must be a valid string.',
            'personal_details.secondary_physical_address.string' => 'The secondary physical address must be a valid string.',
            'personal_details.postal_address.string' => 'The postal address must be a valid string.',
            'personal_details.identification_number.required' => 'The identification number is required.',
            'personal_details.identification_number.string' => 'The identification number must be a valid string.',
            'personal_details.identification_number.max' => 'The identification number must not exceed 255 characters.',
            'personal_details.tax_identification_pin.required' => 'The tax identification PIN is required.',
            'personal_details.tax_identification_pin.string' => 'The tax identification PIN must be a valid string.',
            'personal_details.tax_identification_pin.max' => 'The tax identification PIN must not exceed 255 characters.',
        ];

        $request->validate([
            'personal_details.first_name' => ['required', 'string', 'max:255'],
            'personal_details.middle_name' => ['nullable', 'string', 'max:255'],
            'personal_details.last_name' => ['required', 'string', 'max:255'],
            'personal_details.honorific_id' => ['nullable', Rule::exists('honorifics', 'id')],
            'personal_details.marital_status_id' => ['required', Rule::exists('marital_statuses', 'id')],
            'personal_details.gender_id' => ['required', Rule::exists('genders', 'id')],
            'personal_details.religion_id' => ['required', Rule::exists('religions', 'id')],
            'personal_details.email' => ['nullable', 'email', 'max:255'],
            'personal_details.primary_phone' => ['required', 'string', 'max:255'],
            'personal_details.secondary_phone' => ['nullable', 'string', 'max:255'],
            'personal_details.permanent_physical_address' => ['required', 'string'],
            'personal_details.secondary_physical_address' => ['nullable', 'string'],
            'personal_details.postal_address' => ['nullable', 'string'],
            'personal_details.identification_number' => ['required', 'string', 'max:255'],
            'personal_details.tax_identification_pin' => ['required', 'string', 'max:255'],
        ], $errorMessages);

        if($employee)
        {
            return to_route('teachers.edit', $employee);
        }

        return to_route('teachers.create');
    }

    public function secondStep(Request $request, Employee $employee = null)
    {
        $errorMessages = [
            'employee_details.staff_number.string' => 'The staff number must be a valid string.',
            'employee_details.staff_number.max' => 'The staff number must not exceed 255 characters.',
            'employee_details.date_of_hire.date' => 'The date of hire must be a valid date.',
            'employee_details.employment_type_id.required' => 'The employment type is required.',
            'employee_details.employment_type_id.exists' => 'The selected employment type is invalid.',
            'employee_details.employment_status_id.required' => 'The employment status is required.',
            'employee_details.employment_status_id.exists' => 'The selected employment status is invalid.',
            'employee_details.job_title_id.required' => 'The job title is required.',
            'employee_details.job_title_id.exists' => 'The selected job title is invalid.',
            'employee_details.emergency_contacts.array' => 'The emergency contacts must be an array.',
            'employee_details.emergency_contacts.*.name.string' => 'Each emergency contact name must be a valid string.',
            'employee_details.emergency_contacts.*.name.max' => 'Each emergency contact name must not exceed 255 characters.',
            'employee_details.emergency_contacts.*.email.string' => 'Each emergency contact email must be a valid string.',
            'employee_details.emergency_contacts.*.email.max' => 'Each emergency contact email must not exceed 255 characters.',
            'employee_details.emergency_contacts.*.phone.string' => 'Each emergency contact phone must be a valid string.',
            'employee_details.emergency_contacts.*.phone.max' => 'Each emergency contact phone must not exceed 255 characters.',
            'employee_details.emergency_contacts.*.relationship_id.exists' => 'Each emergency contact relationship must be valid.',
        ];

        $request->validate([
            'employee_details.staff_number' => ['nullable', 'string', 'max:255'],
            'employee_details.date_of_hire' => ['nullable', 'date'],
            'employee_details.employment_type_id' => ['required', Rule::exists('employment_types', 'id')],
            'employee_details.employment_status_id' => ['required', Rule::exists('employment_statuses', 'id')],
            'employee_details.job_title_id' => ['required', Rule::exists('job_titles', 'id')],
            'employee_details.emergency_contacts' => ['nullable', 'array'],
            'employee_details.emergency_contacts.*.name' => ['nullable', 'string', 'max:255'],
            'employee_details.emergency_contacts.*.email' => ['nullable', 'string', 'max:255'],
            'employee_details.emergency_contacts.*.phone' => ['nullable', 'string', 'max:255'],
            'employee_details.emergency_contacts.*.relationship_id' => ['nullable', Rule::exists('relationships', 'id')],
        ], $errorMessages);

        if($employee)
        {
            return to_route('teachers.edit', $employee);
        }

        return to_route('teachers.create');
    }

    public function thirdStep(Request $request)
    {
        $errorMessages =[];

        $request->validate([
            'other_details.specialization_area_id' => ['required', Rule::exists('specializations', 'id')],
            'other_details.teacher_title_id' => ['required', Rule::exists('teacher_titles', 'id')],
            'other_details.tsc_number' => ['required', 'string', 'max:255'],
            'other_details.years_of_experience' => ['nullable', 'integer', 'min:0'],
            'other_details.qualifications' => ['nullable', 'array'],
            'other_details.qualifications.*.institution_name' => ['nullable', 'string', 'max:255'],
            'other_details.qualifications.*.course_name' => ['nullable', 'string', 'max:255'],
            'other_details.qualifications.*.year_of_completion' => ['nullable', 'string', 'max:255'],
            'other_details.qualifications.*.qualification_type_id' => ['nullable', Rule::exists('qualification_types', 'id')],
            'other_details.work_histories' => ['nullable', 'array'],
            'other_details.work_histories.*.institution_name' => ['nullable', 'string', 'max:255'],
            'other_details.work_histories.*.start_date' => ['nullable', 'date'],
            'other_details.work_histories.*.end_date' => ['nullable', 'date'],
//            'other_details.work_histories.*.year_of_completion' => ['nullable', 'string', 'max:255'],
        ], $errorMessages);

        return to_route('teachers.create');
    }

    public function otherDetailsValidation(Request $request)
    {
        return $request->validate([
            'other_details.specialization_area_id' => ['required', Rule::exists('specialization_areas', 'id')],
            'other_details.teacher_title_id' => ['nullable', Rule::exists('teacher_titles', 'id')],
            'other_details.tsc_number' => ['required', 'string', 'max:255'],
            'other_details.years_of_experience' => ['nullable', 'integer', 'min:0'],
            'other_details.qualifications' => ['nullable', 'array'],
            'other_details.qualifications.*.institution_name' => ['nullable', 'string', 'max:255'],
            'other_details.qualifications.*.course_name' => ['nullable', 'string', 'max:255'],
            'other_details.qualifications.*.year_of_completion' => ['nullable', 'string', 'max:255'],
            'other_details.qualifications.*.qualification_type_id' => ['nullable', Rule::exists('qualification_types', 'id')],
            'other_details.work_histories' => ['nullable', 'array'],
            'other_details.work_histories.*.institution_name' => ['nullable', 'string', 'max:255'],
            'other_details.work_histories.*.start_date' => ['nullable', 'date'],
            'other_details.work_histories.*.end_date' => ['nullable', 'date'],
        ]);
    }

//    /**
//     * @param $qualifications
//     * @param Employee $employee
//     * @return void
//     */
//    public function insertQualifications($qualifications, Employee $employee): void
//    {
//        $qualifications = collect($qualifications['other_details']['qualifications'])
//            ->filter(function ($qualification) {
//                return isset($qualification['institution_name']);
//            })
//            ->map(function ($qualification) use ($employee) {
//                return [
//                    'employee_id' => $employee->id,
//                    'institution_name' => $qualification['institution_name'],
//                    'course_name' => $qualification['course_name'],
//                    'year_of_completion' => $qualification['year_of_completion'],
//                    'qualification_type_id' => $qualification['qualification_type_id'],
//                ];
//            })->toArray();
//
//        if (!empty($qualifications)) {
//
//            Qualification::insert($qualifications);
//        }
//    }
}
