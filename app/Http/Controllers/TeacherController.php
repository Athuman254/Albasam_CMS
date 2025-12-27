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
        Log::info('🎯 TEACHER CONTROLLER INDEX METHOD CALLED');
        Log::info('📁 Component path: Admin/Employees/Teachers/Index');
        Log::info('🌐 URL: ' . request()->fullUrl());
        Log::info('👤 User: ' . (auth()->user() ? auth()->user()->email : 'Not authenticated'));
        Log::info('🔍 IP: ' . request()->ip());
        Log::info('🕒 Time: ' . now());

        // Test if we can reach this point
        try {
            Log::info('🔄 Attempting Inertia render...');
            $response = Inertia::render('Admin/Employees/Teachers/Index', []);
            Log::info('✅ INERTIA RENDER SUCCESS - Teacher index page should load');
            return $response;
        } catch (\Exception $e) {
            Log::error('❌ INERTIA RENDER FAILED: ' . $e->getMessage());
            Log::error('📝 Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    public function create(): \Inertia\Response
    {
        Log::info('🎯 TEACHER CONTROLLER CREATE METHOD CALLED');
        Log::info('📁 Component path: Admin/Employees/Teachers/Create');

        try {
            $subjects = \App\Models\Subject::where('activated', true)->orderBy('name')->get();

            $response = Inertia::render('Admin/Employees/Teachers/Create', [
                'subjects' => $subjects
            ]);
            Log::info('✅ INERTIA RENDER SUCCESS - Teacher create page should load');
            return $response;
        } catch (\Exception $e) {
            Log::error('❌ INERTIA RENDER FAILED: ' . $e->getMessage());
            throw $e;
        }
    }

    public function store(TeacherRequest $request): \Illuminate\Http\RedirectResponse
    {
        Log::info('🎯 TEACHER CONTROLLER STORE METHOD CALLED');
        Log::info('📝 Storing new teacher data');

        $validatedData = $request->validated();

        DB::beginTransaction();

        try {
            Log::info('🔄 Creating employee record...');
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
                'staff_number' => Employee::generateStaffNumber(),
                'tsc_number' => $validatedData['other_details']['tsc_number'] ?? null,
                'hobbies' => $validatedData['other_details']['hobbies'] ?? null,
                'date_of_hire' => $validatedData['employee_details']['date_of_hire'],
                'employment_status_id' => $validatedData['employee_details']['employment_status_id'],
                'employment_type_id' => $validatedData['employee_details']['employment_type_id'],
            ]);

            // Handle Photo Upload
            if ($request->hasFile('photo')) {
                $employee->addMediaFromRequest('photo')
                    ->toMediaCollection('employee_photos');
            }

            // Handle Documents Upload
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $employee->addMedia($file)
                        ->toMediaCollection('employee_documents');
                }
            }

            Log::info('✅ Employee created with ID: ' . $employee->id);

            Log::info('🔄 Creating teacher record...');
            $teacher = Teacher::create([
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

            Log::info('✅ Teacher created with ID: ' . $teacher->id);

            if (isset($validatedData['employee_details']['emergency_contacts']) && is_array($validatedData['employee_details']['emergency_contacts'])) {
                Log::info('🔄 Processing emergency contacts...');
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

                if (!empty($emergencyContacts)) {
                    EmergencyContact::insert($emergencyContacts);
                    Log::info('✅ Emergency contacts created: ' . count($emergencyContacts));
                }
            }

            if (isset($validatedData['other_details']['qualifications']) && is_array($validatedData['other_details']['qualifications'])) {
                Log::info('🔄 Processing qualifications...');
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
                    Log::info('✅ Qualifications created: ' . count($teacherQualifications));
                }
            }

            if (isset($validatedData['other_details']['work_histories']) && !empty($validatedData['other_details']['work_histories']) && is_array($validatedData['other_details']['work_histories'])) {
                Log::info('🔄 Processing work histories...');
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

                if (!empty($workHistories)) {
                    WorkHistory::insert($workHistories);
                    Log::info('✅ Work histories created: ' . count($workHistories));
                }
            }

            if (isset($validatedData['teaching_subjects']) && is_array($validatedData['teaching_subjects'])) {
                Log::info('🔄 Syncing teaching subjects...');
                $teacher->teachingSubjects()->sync($validatedData['teaching_subjects']);
                Log::info('✅ Teaching subjects synced: ' . count($validatedData['teaching_subjects']));
            }

            DB::commit();
            Log::info('🎉 TEACHER CREATION SUCCESSFUL - Redirecting to index');

            return to_route('admin.teachers.index');
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('❌ TEACHER CREATION FAILED: ' . $exception->getMessage());
            Log::error('📝 Stack trace: ' . $exception->getTraceAsString());
            report($exception);
            return redirect()->back()->withInput()->withErrors(['message' => 'Failed to save teacher details. Please try again.']);
        }
    }

    public function show(Teacher $teacher): \Inertia\Response
    {
        Log::info('🎯 TEACHER CONTROLLER SHOW METHOD CALLED');
        Log::info('📁 Teacher ID: ' . $teacher->id);

        $teacher->load('specialization', 'job', 'user');
        $employee = Employee::findOrFail($teacher->employee_id);
        $employee->load('employment_type', 'employment_status', 'honorific', 'marital_status', 'gender', 'religion', 'teacher', 'user');

        Log::info('🔄 Rendering teacher show page...');

        return Inertia::render('Admin/Employees/Teachers/Show', [
            'teacher' => $teacher,
            'employee' => $employee,
        ]);
    }

    public function edit(Teacher $teacher): \Inertia\Response
    {
        Log::info('🎯 TEACHER CONTROLLER EDIT METHOD CALLED');
        Log::info('📁 Teacher ID: ' . $teacher->id);

        $employee = Employee::findOrFail($teacher->employee_id);

        // Get all available subjects
        $subjects = \App\Models\Subject::where('activated', true)->orderBy('name')->get();

        // Get teacher's current teaching subjects
        $currentSubjects = $teacher->teachingSubjects()->pluck('subjects.id')->toArray();

        Log::info('🔄 Rendering teacher edit page...');

        return Inertia::render('Admin/Employees/Teachers/Edit', [
            'teacher' => $teacher,
            'employee' => $employee,
            'subjects' => $subjects,
            'currentSubjects' => $currentSubjects,
        ]);
    }

    public function update(Teacher $teacher, TeacherRequest $request): \Illuminate\Http\RedirectResponse
    {
        Log::info('🎯 TEACHER CONTROLLER UPDATE METHOD CALLED');
        Log::info('📁 Teacher ID: ' . $teacher->id);

        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $employee = Employee::findOrFail($teacher->employee_id);
            Log::info('🔄 Updating employee record...');

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
                'tsc_number' => $validated['other_details']['tsc_number'] ?? null,
                'hobbies' => $validated['other_details']['hobbies'] ?? null,
                'tax_identification_pin' => $validated['personal_details']['tax_identification_pin'],
                'date_of_hire' => $validated['employee_details']['date_of_hire'],
                'employment_status_id' => $validated['employee_details']['employment_status_id'],
                'employment_type_id' => $validated['employee_details']['employment_type_id'],
                'in_payroll' => $validated['other_details']['in_payroll'] ?? false,
                'pays_paye' => $validated['other_details']['pays_paye'] ?? false,
                'pays_sha' => $validated['other_details']['pays_sha'] ?? false,
                'sha_no' => $validated['other_details']['sha_no'] ?? '',
                'pays_nssf' => $validated['other_details']['pays_nssf'] ?? false,
                'nssf_no' => $validated['other_details']['nssf_no'] ?? '',
                'pays_housing_levy' => $validated['other_details']['pays_housing_levy']
            ]);

            // Handle Photo Upload
            if ($request->hasFile('photo')) {
                $employee->clearMediaCollection('employee_photos');
                $employee->addMediaFromRequest('photo')
                    ->toMediaCollection('employee_photos');
            }

            // Handle Documents Upload
            if ($request->hasFile('documents')) {
                // For updates, we might want to append or replace. Let's append for now.
                foreach ($request->file('documents') as $file) {
                    $employee->addMedia($file)
                        ->toMediaCollection('employee_documents');
                }
            }

            Log::info('🔄 Updating teacher record...');
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
                Log::info('🔄 Updating emergency contacts...');
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
                    Log::info('✅ Emergency contacts updated: ' . count($emergencyContacts));
                }
            }

            if (isset($validated['other_details']['qualifications']) && is_array($validated['other_details']['qualifications'])) {
                Log::info('🔄 Updating qualifications...');
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
                    Log::info('✅ Qualifications updated: ' . count($qualifications));
                }
            }

            if (!empty($validated['other_details']['work_histories']) && is_array($validated['other_details']['work_histories'])) {
                Log::info('🔄 Updating work histories...');
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

                if (!empty($workHistories)) {
                    WorkHistory::insert($workHistories);
                    Log::info('✅ Work histories updated: ' . count($workHistories));
                }
            }

            // Sync teaching subjects
            if (isset($validated['teaching_subjects'])) {
                Log::info('🔄 Syncing teaching subjects...');
                $teacher->teachingSubjects()->sync($validated['teaching_subjects']);
                Log::info('✅ Teaching subjects synced: ' . count($validated['teaching_subjects']));
            }

            DB::commit();
            Log::info('🎉 TEACHER UPDATE SUCCESSFUL - Redirecting to index');

            return to_route('admin.teachers.index');
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('❌ TEACHER UPDATE FAILED: ' . $exception->getMessage());
            Log::error('📝 Stack trace: ' . $exception->getTraceAsString());
            report($exception);
            return redirect()->back()->withInput()->withErrors(['message' => 'Failed to update teacher details. Please try again.']);
        }
    }
}
