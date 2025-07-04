<?php

namespace App\Http\Controllers;

use App\Http\Requests\Employees\EmployeeCredentialRequest;
use App\Http\Requests\TeacherRequest;
use App\Http\Resources\Resource;
use App\Models\EmergencyContact;
use App\Models\Employee;
use App\Models\Qualification;
use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use App\Models\WorkHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
            Teacher::with(['employee.employment_type', 'employee.employment_status', 'honorific', 'specialization', 'job', 'user'])
                ->orderBy('first_name')
        )->allowedFilters([
            AllowedFilter::scope('search', 'Search'),
        ])->jsonPaginate();

        return Resource::collection($teachers);
    }

    public function index(): \Inertia\Response
    {
        return Inertia::render('Admin/Employees/Teachers/Index', []);
    }

    public function create(): \Inertia\Response
    {
        return Inertia::render('Admin/Employees/Teachers/Create');
    }

    public function store(TeacherRequest $request): \Illuminate\Http\RedirectResponse
    {
        $validatedData = $request->validated();
        
//        dd($validatedData);

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
//                'staff_number' => $validatedData['employee_details']['staff_number'],
                'staff_number' => Employee::generateStaffNumber(),
                'date_of_hire' => $validatedData['employee_details']['date_of_hire'],
                'employment_status_id' => $validatedData['employee_details']['employment_status_id'],
                'employment_type_id' => $validatedData['employee_details']['employment_type_id'],
            ]);

            Teacher::create([
                'employee_id' => $employee->id,
                'first_name' => $validatedData['personal_details']['first_name'],
                'middle_name' => $validatedData['personal_details']['middle_name'],
                'last_name' => $validatedData['personal_details']['last_name'],
                'honorific_id' => $validatedData['personal_details']['honorific_id'],
                'job_title_id' => $validatedData['other_details']['job_title_id'],
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
            return to_route('admin.teachers.index');

        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Error: ' . $exception->getMessage());
            report($exception);
            return redirect()->back()->withInput()->withErrors(['message' => 'Failed to save teacher details. Please try again.']);
        }
    }

    public function show(Teacher $teacher): \Inertia\Response
    {
        $teacher->load('specialization', 'job', 'user');
        $employee = Employee::findOrFail($teacher->employee_id);
        $employee->load('employment_type', 'employment_status', 'honorific', 'marital_status', 'gender', 'religion', 'teacher', 'user');

        return Inertia::render('Admin/Employees/Teachers/Show', [
            'teacher' => $teacher,
            'employee' => $employee,
        ]);
    }

    public function edit(Teacher $teacher): \Inertia\Response
    {
        $employee = Employee::findOrFail($teacher->employee_id);

        return Inertia::render('Admin/Employees/Teachers/Edit', [
            'teacher' => $teacher,
            'employee' => $employee,
        ]);
    }

    public function update(Teacher $teacher, TeacherRequest $request): \Illuminate\Http\RedirectResponse
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
//                'staff_number' => $validated['employee_details']['staff_number'],
                'date_of_hire' => $validated['employee_details']['date_of_hire'],
                'employment_status_id' => $validated['employee_details']['employment_status_id'],
                'employment_type_id' => $validated['employee_details']['employment_type_id'],
            ]);

            $teacher->update([
                'first_name' => $validated['personal_details']['first_name'],
                'middle_name' => $validated['personal_details']['middle_name'],
                'last_name' => $validated['personal_details']['last_name'],
                'honorific_id' => $validated['personal_details']['honorific_id'],
                'specialization_area_id' => $validated['other_details']['specialization_area_id'],
                'job_title_id' => $validated['other_details']['job_title_id'],
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
            return to_route('admin.teachers.index');

        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Error: ' . $exception->getMessage());
            report($exception);
            return redirect()->back()->withInput()->withErrors(['message' => 'Failed to update teacher details. Please try again.']);
        }
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
