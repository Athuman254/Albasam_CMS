<?php

namespace App\Http\Controllers;

use App\Http\Requests\Employees\EmployeeCredentialRequest;
use App\Http\Resources\Resource;
use App\Models\Employee;
use App\Models\Rank;
use App\Models\Subject;
use App\Models\Settings\AcademicYear;
use App\Models\EmployeeClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the employees.
     */
    public function index(Request $request): Response
    {
        // Get all roles except admin for the modal
        $roles = \App\Models\Role::where('name', '!=', 'admin')
            ->get(['id', 'name', 'display_name', 'description']);

        // Get subjects for specialization dropdown
        $subjects = Subject::select('id', 'name')->orderBy('name')->get();

        // Get all other dropdown options
        $employmentTypes = \App\Models\EmploymentType::select('id', 'name')->get();
        $employmentStatuses = \App\Models\EmploymentStatus::select('id', 'name')->get();
        $honorifics = \App\Models\Honorific::select('id', 'name')->get();
        $maritalStatuses = \App\Models\MaritalStatus::select('id', 'name')->get();
        $genders = \App\Models\Gender::select('id', 'name')->get();
        $religions = \App\Models\Religion::select('id', 'name')->get();

        // Generate next staff number
        $nextStaffNumber = Employee::generateStaffNumber();

        // Build query with relationships
        $query = Employee::query()->with([
            'employmentType',
            'employmentStatus',
            'gender',
            'honorific',
            'classes'
        ]);

        // Add search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('staff_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('primary_phone', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
            });
        }

        return Inertia::render('Admin/Employees/Index', [
            'employees' => $query->latest()->paginate($request->input('per_page', 20)),
            'filters' => $request->all(['search', 'trashed']),
            'roles' => $roles,
            'subjects' => $subjects,
            'nextStaffNumber' => $nextStaffNumber,
            'options' => [
                'employmentTypes' => $employmentTypes,
                'employmentStatuses' => $employmentStatuses,
                'honorifics' => $honorifics,
                'maritalStatuses' => $maritalStatuses,
                'genders' => $genders,
                'religions' => $religions,
            ]
        ]);
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create(): Response
    {
        // Get all roles except admin (admin role should be assigned carefully)
        $roles = \App\Models\Role::where('name', '!=', 'admin')
            ->get(['id', 'name', 'display_name', 'description']);

        return Inertia::render('Admin/Employees/Create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Personal Info
            'honorific_id' => 'nullable|exists:honorifics,id',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender_id' => 'required|exists:genders,id',
            'marital_status_id' => 'nullable|exists:marital_statuses,id',
            'religion_id' => 'nullable|exists:religions,id',
            'date_of_hire' => 'required|date',

            // Contact Info
            'email' => 'nullable|email|max:255|unique:employees,email',
            'primary_phone' => 'required|string|max:20|unique:employees,primary_phone',
            'secondary_phone' => 'nullable|string|max:20',
            'permanent_physical_address' => 'nullable|string',
            'secondary_physical_address' => 'nullable|string',
            'postal_address' => 'nullable|string',

            // Employment Info
            'staff_number' => 'required|string|unique:employees',
            'employment_type_id' => 'required|exists:employment_types,id',
            'employment_status_id' => 'required|exists:employment_statuses,id',
            'identification_number' => 'required|string|max:50|unique:employees',
            'tax_identification_pin' => 'nullable|string|max:50',

            // Role & System Access
            'role_id' => 'required|exists:roles,id',
            'has_system_access' => 'boolean',
            'password' => 'required_if:has_system_access,true|min:8',

            // Teacher Specific
            'subject_specialization' => 'nullable|array',
            'teaching_qualification' => 'nullable|string|max:255',

            // Payroll Info
            'in_payroll' => 'boolean',
            'pays_paye' => 'boolean',
            'pays_sha' => 'boolean',
            'sha_no' => 'nullable|string|max:50',
            'pays_nssf' => 'boolean',
            'nssf_no' => 'nullable|string|max:50',
            'pays_housing_levy' => 'boolean',
        ], [
            // Custom error messages
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'gender_id.required' => 'Gender is required.',
            'date_of_hire.required' => 'Date of hire is required.',
            'primary_phone.required' => 'Primary phone number is required.',
            'primary_phone.unique' => 'This phone number is already registered.',
            'email.unique' => 'This email is already registered.',
            'staff_number.unique' => 'This staff number is already in use.',
            'identification_number.unique' => 'This ID number is already registered.',
            'employment_type_id.required' => 'Employment type is required.',
            'employment_status_id.required' => 'Employment status is required.',
            'role_id.required' => 'Staff role is required.',
        ]);

        // Check for soft-deleted duplicates
        $conflicts = Employee::withTrashed()
            ->where(function ($query) use ($request) {
                $query->where('primary_phone', $request->primary_phone)
                    ->orWhere('email', $request->email)
                    ->orWhere('staff_number', $request->staff_number)
                    ->orWhere('identification_number', $request->identification_number);
            })
            ->whereNotNull('deleted_at')
            ->first();

        if ($conflicts) {
            $field = '';
            if ($conflicts->primary_phone === $request->primary_phone) $field = 'phone number';
            elseif ($conflicts->email === $request->email) $field = 'email';
            elseif ($conflicts->staff_number === $request->staff_number) $field = 'staff number';
            elseif ($conflicts->identification_number === $request->identification_number) $field = 'ID number';

            return back()
                ->withInput()
                ->withErrors(['error' => "This {$field} belongs to a deleted employee ({$conflicts->first_name} {$conflicts->last_name}). Please restore them from the trash or permanently delete them first."]);
        }

        DB::beginTransaction();
        try {
            // Convert empty strings to null for optional fields
            $nullableFields = [
                'honorific_id',
                'middle_name',
                'marital_status_id',
                'religion_id',
                'email',
                'secondary_phone',
                'permanent_physical_address',
                'secondary_physical_address',
                'postal_address',
                'tax_identification_pin',
                'teaching_qualification',
                'sha_no',
                'nssf_no'
            ];

            foreach ($nullableFields as $field) {
                if (isset($validated[$field]) && $validated[$field] === '') {
                    $validated[$field] = null;
                }
            }

            // Handle subject specialization array -> string
            if (isset($validated['subject_specialization']) && is_array($validated['subject_specialization'])) {
                $validated['subject_specialization'] = implode(', ', $validated['subject_specialization']);
            }

            $employee = Employee::create($validated);

            // Create user account if system access granted
            if ($request->boolean('has_system_access')) {
                $user = \App\Models\User::create([
                    'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                    'email' => $validated['email'],
                    'password' => bcrypt($validated['password']),
                    'username' => strtolower($validated['first_name'] . '.' . $validated['last_name']),
                ]);

                // Link employee to user
                $employee->user_id = $user->id;
                $employee->save();

                // Assign role
                $user->roles()->attach($validated['role_id']);
            } elseif (!empty($validated['role_id']) && $employee->user) {
                // If user already exists (shouldn't happen in create but good fallback)
                $employee->user->roles()->sync([$validated['role_id']]);
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'Employee created successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            // Handle specific database errors with user-friendly messages
            if ($e->getCode() == 23000) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'This employee information already exists. Please check phone number, email, or ID number.');
            }

            Log::error('Employee creation failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create employee. Please check all required fields and try again.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Employee creation failed: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the employee. Please try again.');
        }
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee): Response
    {
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

        // Load current assignments for the employee
        $currentAssignments = EmployeeClass::with(['class.stream', 'subject', 'academicYear'])
            ->where('employee_id', $employee->id)
            ->when($currentAcademicYear, function ($query) use ($currentAcademicYear) {
                return $query->where('academic_year_id', $currentAcademicYear->id);
            })
            ->get();

        return Inertia::render('Admin/Employees/Show', [
            'employee' => $employee->load([
                'user',
                'employmentType',
                'employmentStatus',
                'gender',
                'classes',
                'subjects'
            ]),
            'currentAssignments' => $currentAssignments,
            'currentAcademicYear' => $currentAcademicYear,
        ]);
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Employee $employee): Response
    {
        // Get all roles except admin
        $roles = \App\Models\Role::where('name', '!=', 'admin')
            ->get(['id', 'name', 'display_name', 'description']);

        // Get employee's current role
        $currentRole = $employee->user?->roles->first();

        return Inertia::render('Admin/Employees/Edit', [
            'employee' => $employee->load([
                'user',
                'employmentType',
                'employmentStatus',
                'gender'
            ]),
            'roles' => $roles,
            'currentRole' => $currentRole,
        ]);
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            // Personal Info
            'honorific_id' => 'nullable|exists:honorifics,id',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender_id' => 'required|exists:genders,id',
            'marital_status_id' => 'nullable|exists:marital_statuses,id',
            'religion_id' => 'nullable|exists:religions,id',
            'date_of_hire' => 'required|date',

            // Contact Info
            'email' => 'nullable|email|max:255|unique:employees,email,' . $employee->id,
            'primary_phone' => 'required|string|max:20|unique:employees,primary_phone,' . $employee->id,
            'secondary_phone' => 'nullable|string|max:20',
            'permanent_physical_address' => 'nullable|string',
            'secondary_physical_address' => 'nullable|string',
            'postal_address' => 'nullable|string',

            // Employment Info
            'staff_number' => 'required|string|unique:employees,staff_number,' . $employee->id,
            'employment_type_id' => 'required|exists:employment_types,id',
            'employment_status_id' => 'required|exists:employment_statuses,id',
            'identification_number' => 'required|string|max:50|unique:employees,identification_number,' . $employee->id,
            'tax_identification_pin' => 'nullable|string|max:50',

            // Role & System Access
            'role_id' => 'required|exists:roles,id',
            'has_system_access' => 'boolean',
            'password' => 'nullable|min:8',

            // Teacher Specific
            'subject_specialization' => 'nullable|array',
            'teaching_qualification' => 'nullable|string|max:255',

            // Payroll Info
            'in_payroll' => 'boolean',
            'pays_paye' => 'boolean',
            'pays_sha' => 'boolean',
            'sha_no' => 'nullable|string|max:50',
            'pays_nssf' => 'boolean',
            'nssf_no' => 'nullable|string|max:50',
            'pays_housing_levy' => 'boolean',
        ], [
            // Custom error messages
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'gender_id.required' => 'Gender is required.',
            'date_of_hire.required' => 'Date of hire is required.',
            'primary_phone.required' => 'Primary phone number is required.',
            'primary_phone.unique' => 'This phone number is already registered.',
            'email.unique' => 'This email is already registered.',
            'staff_number.unique' => 'This staff number is already in use.',
            'identification_number.unique' => 'This ID number is already registered.',
            'employment_type_id.required' => 'Employment type is required.',
            'employment_status_id.required' => 'Employment status is required.',
            'role_id.required' => 'Staff role is required.',
        ]);

        DB::beginTransaction();
        try {
            // Convert empty strings to null for optional fields
            $nullableFields = [
                'honorific_id',
                'middle_name',
                'marital_status_id',
                'religion_id',
                'email',
                'secondary_phone',
                'permanent_physical_address',
                'secondary_physical_address',
                'postal_address',
                'tax_identification_pin',
                'teaching_qualification',
                'sha_no',
                'nssf_no'
            ];

            foreach ($nullableFields as $field) {
                if (isset($validated[$field]) && $validated[$field] === '') {
                    $validated[$field] = null;
                }
            }

            // Handle subject specialization array -> string
            if (isset($validated['subject_specialization']) && is_array($validated['subject_specialization'])) {
                $validated['subject_specialization'] = implode(', ', $validated['subject_specialization']);
            }

            $employee->update($validated);

            // Update role if provided and employee has a user account
            if ($employee->user) {
                if (!empty($validated['role_id'])) {
                    // Sync roles (remove old, add new)
                    $employee->user->roles()->sync([$validated['role_id']]);
                } else {
                    // Remove all roles if role_id is empty
                    $employee->user->roles()->detach();
                }

                // Update password if provided
                if (!empty($validated['password'])) {
                    $employee->user->update([
                        'password' => bcrypt($validated['password']),
                    ]);
                }
            } elseif ($request->boolean('has_system_access') && !empty($validated['password'])) {
                // Create user if they don't have one but system access is requested
                $user = \App\Models\User::create([
                    'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                    'email' => $validated['email'],
                    'password' => bcrypt($validated['password']),
                    'username' => strtolower($validated['first_name'] . '.' . $validated['last_name']),
                ]);

                $employee->user_id = $user->id;
                $employee->save();

                $user->roles()->attach($validated['role_id']);
            }

            DB::commit();

            // For Inertia requests, we need to handle this differently
            // Instead of redirecting, we'll let Inertia handle the response
            return back()->with('success', 'Employee updated successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            // Handle specific database errors with user-friendly messages
            if ($e->getCode() == 23000) {
                return back()
                    ->withInput()
                    ->withErrors(['error' => 'This employee information already exists. Please check phone number, email, or ID number.']);
            }

            Log::error('Employee update failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update employee. Please check all required fields and try again.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Employee update failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update employee: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(Employee $employee): RedirectResponse
    {
        DB::beginTransaction();
        try {
            // Store user_id before removing the reference
            $userId = $employee->user_id;

            // Set user_id to NULL to remove the foreign key reference
            $employee->user_id = null;
            $employee->save();

            // Now delete the employee
            $employee->delete();

            // Then delete the associated user if exists
            if ($userId) {
                $user = \App\Models\User::find($userId);
                if ($user) {
                    $user->delete();
                }
            }

            DB::commit();
            return back()->with('success', 'Employee deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Employee deletion failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete employee: ' . $e->getMessage());
        }
    }

    /**
     * Get employees for datatable
     */
    public function dataTable(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $employees = QueryBuilder::for(
            Employee::with([
                'employmentType',
                'employmentStatus',
                'honorific',
                'maritalStatus',
                'gender',
                'religion',
                'teacher',
                'classes' => function ($query) {
                    $academicYearId = AcademicYear::where('is_active', true)->value('id');
                    if ($academicYearId) {
                        $query->wherePivot('academic_year_id', $academicYearId);
                    }
                }
            ])->orderBy('first_name')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::scope('search', 'Search'),
            AllowedFilter::scope('teachers', 'Teachers'),
            AllowedFilter::scope('active', 'Active'),
        ])->jsonPaginate();

        return Resource::collection($employees);
    }

    /**
     * Datatable for employee-class assignments
     */
    public function dataTableEmployeeClasses(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $assignments = QueryBuilder::for(
            EmployeeClass::query()
                ->join('employees', 'employee_class.employee_id', '=', 'employees.id')
                ->join('ranks', 'employee_class.class_id', '=', 'ranks.id')
                ->leftJoin('subjects', 'employee_class.subject_id', '=', 'subjects.id')
                ->join('academic_years', 'employee_class.academic_year_id', '=', 'academic_years.id')
                ->select(
                    'employee_class.*',
                    'employees.first_name',
                    'employees.last_name',
                    'employees.staff_number',
                    'ranks.name as class_name',
                    'subjects.name as subject_name',
                    'academic_years.name as academic_year_name'
                )
        )->allowedFilters([
            AllowedFilter::exact('employee_id'),
            AllowedFilter::exact('class_id'),
            AllowedFilter::exact('academic_year_id'),
            AllowedFilter::callback('search', function ($query, $value) {
                $query->where(function ($q) use ($value) {
                    $q->where('employees.first_name', 'like', "%{$value}%")
                        ->orWhere('employees.last_name', 'like', "%{$value}%")
                        ->orWhere('employees.staff_number', 'like', "%{$value}%")
                        ->orWhere('ranks.name', 'like', "%{$value}%")
                        ->orWhere('subjects.name', 'like', "%{$value}%");
                });
            }),
        ])->jsonPaginate();

        return Resource::collection($assignments);
    }

    /**
     * Datatable for employee subject assignments
     */
    public function dataTableEmployeeSubjects(): JsonResponse
    {
        // Implement your datatable logic for subject assignments
        return response()->json([]);
    }

    /**
     * Grant system access to employee - FIXED VERSION
     * Uses plain text password to let Employee model mutator handle hashing
     */
    public function systemAccess(EmployeeCredentialRequest $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validated();

        $update = ['has_system_access' => $validated['has_system_access']];

        if (!empty($validated['password'])) {
            // ✅ FIXED: Use plain text password - let the Employee model mutator handle hashing
            $update['password'] = $validated['password'];
        }

        $employee->update($update);

        return back(303)->with('success', 'Credentials captured.');
    }

    /**
     * Revoke system access from employee
     */
    public function revokeSystemAccess(Employee $employee): RedirectResponse
    {
        $employee->update([
            'has_system_access' => false,
        ]);

        return back(303)->with('success', 'Access Revoked');
    }

    /**
     * Show form for assigning classes to employee
     */
    public function assignClasses(Employee $employee): Response
    {
        // Load classes with their stream relationship
        $classes = Rank::with('stream')->where('activated', true)->get();
        $subjects = Subject::where('activated', true)->get();
        $academicYears = AcademicYear::all();
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();

        // Get current assignments using the EmployeeClass model
        $currentAssignments = EmployeeClass::with(['class.stream', 'subject', 'academicYear'])
            ->where('employee_id', $employee->id)
            ->when($currentAcademicYear, function ($query) use ($currentAcademicYear) {
                return $query->where('academic_year_id', $currentAcademicYear->id);
            })
            ->get();

        return Inertia::render('Admin/Employees/AssignClasses', [
            'employee' => $employee->load([
                'user',
                'employmentType',
                'employmentStatus',
                'honorific'
            ]),
            'classes' => $classes,
            'subjects' => $subjects,
            'academicYears' => $academicYears,
            'currentAcademicYear' => $currentAcademicYear,
            'currentAssignments' => $currentAssignments,
        ]);
    }

    /**
     * Show the form for assigning subjects to employee
     */
    public function assignSubjects(Employee $employee): Response
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $currentAcademicYear = AcademicYear::where('is_active', true)->first();
        $subjects = Subject::where('activated', true)->orderBy('name')->get();
        $classes = Rank::where('activated', true)->orderBy('name')->get();

        $assignedSubjects = $employee->subjects()
            ->withPivot('academic_year_id', 'class_id')
            ->get();

        return Inertia::render('Admin/Employees/AssignSubjects', [
            'employee' => $employee->load('user', 'gender', 'employmentType', 'employmentStatus'),
            'subjects' => $subjects,
            'classes' => $classes,
            'academicYears' => $academicYears,
            'currentAcademicYear' => $currentAcademicYear,
            'assignedSubjects' => $assignedSubjects,
        ]);
    }

    /**
     * Store class assignments for employee (API endpoint for Vue component)
     * Updated to handle teacher_id field
     */
    public function storeClassAssignments(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:ranks,id',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,id',
            'is_class_teacher' => 'boolean',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        // If subject_ids is provided, is_class_teacher should be false
        if (!empty($validated['subject_ids']) && $validated['is_class_teacher']) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot set both subjects and class teacher for the same assignment.',
            ], 422);
        }

        // If no subjects and not class teacher, return error
        if (empty($validated['subject_ids']) && !$validated['is_class_teacher']) {
            return response()->json([
                'success' => false,
                'message' => 'Please select at least one subject or enable Class Teacher.',
            ], 422);
        }

        // Check subject count limit (max 2 subjects per class)
        if (!empty($validated['subject_ids']) && count($validated['subject_ids']) > 2) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum 2 subjects allowed per class.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // If setting as class teacher
            if ($validated['is_class_teacher']) {
                // Remove existing class teacher for this class
                EmployeeClass::where('class_id', $validated['class_id'])
                    ->where('academic_year_id', $validated['academic_year_id'])
                    ->where('is_class_teacher', true)
                    ->delete();

                // Remove any existing subject assignments for this class
                EmployeeClass::where('employee_id', $employee->id)
                    ->where('class_id', $validated['class_id'])
                    ->where('academic_year_id', $validated['academic_year_id'])
                    ->delete();

                // Create class teacher assignment
                $assignment = EmployeeClass::create([
                    'employee_id' => $employee->id,
                    'teacher_id' => $employee->id, // Explicitly set teacher_id
                    'class_id' => $validated['class_id'],
                    'subject_id' => null,
                    'is_class_teacher' => true,
                    'academic_year_id' => $validated['academic_year_id'],
                ]);

                $assignment->load(['class.stream', 'subject']);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Class Teacher assignment created successfully.',
                    'assignment' => $assignment,
                ]);
            }

            // Handle multiple subject assignments
            $createdAssignments = [];

            // Remove existing subject assignments for this class to avoid duplicates
            EmployeeClass::where('employee_id', $employee->id)
                ->where('class_id', $validated['class_id'])
                ->where('academic_year_id', $validated['academic_year_id'])
                ->delete();

            // Create new subject assignments
            foreach ($validated['subject_ids'] as $subjectId) {
                $assignment = EmployeeClass::create([
                    'employee_id' => $employee->id,
                    'teacher_id' => $employee->id, // Explicitly set teacher_id
                    'class_id' => $validated['class_id'],
                    'subject_id' => $subjectId,
                    'is_class_teacher' => false,
                    'academic_year_id' => $validated['academic_year_id'],
                ]);

                $assignment->load(['class.stream', 'subject']);
                $createdAssignments[] = $assignment;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($createdAssignments) . ' subject assignment(s) created successfully.',
                'assignments' => $createdAssignments,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create class assignments: ' . $e->getMessage(), [
                'employee_id' => $employee->id,
                'request' => $validated,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create assignments: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update class assignment for employee (API endpoint for Vue component)
     * Updated to handle multiple subjects
     */
    public function updateClassAssignment(Request $request, Employee $employee, $assignmentId): JsonResponse
    {
        // Handle bulk update for multiple assignments
        if ($assignmentId === 'bulk') {
            return $this->updateBulkClassAssignments($request, $employee);
        }

        // Handle single assignment update (for backward compatibility)
        $assignment = EmployeeClass::find($assignmentId);

        if (!$assignment || $assignment->employee_id !== $employee->id) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment not found for this employee.',
            ], 404);
        }

        $validated = $request->validate([
            'subject_id' => 'nullable|exists:subjects,id',
            'is_class_teacher' => 'boolean',
        ]);

        // If subject_id is provided, is_class_teacher should be false
        if ($validated['subject_id'] && $validated['is_class_teacher']) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot set both subject and class teacher for the same assignment.',
            ], 422);
        }

        // If setting as class teacher, remove other class teachers for this class
        if ($validated['is_class_teacher']) {
            EmployeeClass::where('class_id', $assignment->class_id)
                ->where('academic_year_id', $assignment->academic_year_id)
                ->where('is_class_teacher', true)
                ->where('id', '!=', $assignment->id)
                ->delete();
        }

        $assignment->update($validated);

        // Load relationships for response
        $assignment->load(['class.stream', 'subject']);

        return response()->json([
            'success' => true,
            'message' => 'Assignment updated successfully.',
            'assignment' => $assignment,
        ]);
    }

    /**
     * Bulk update class assignments for multiple subjects
     */
    private function updateBulkClassAssignments(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:ranks,id',
            'subject_ids' => 'required|array',
            'subject_ids.*' => 'exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'assignment_ids' => 'nullable|array',
            'assignment_ids.*' => 'exists:employee_class,id',
        ]);

        // Check subject count limit
        if (count($validated['subject_ids']) > 2) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum 2 subjects allowed per class.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Remove existing assignments for this class that are not in the new list
            $existingAssignments = EmployeeClass::where('employee_id', $employee->id)
                ->where('class_id', $validated['class_id'])
                ->where('academic_year_id', $validated['academic_year_id'])
                ->get();

            $assignmentsToKeep = [];
            $createdAssignments = [];

            // Update or create assignments for each subject
            foreach ($validated['subject_ids'] as $subjectId) {
                $existingAssignment = $existingAssignments->firstWhere('subject_id', $subjectId);

                if ($existingAssignment) {
                    // Assignment already exists, keep it
                    $existingAssignment->update(['is_class_teacher' => false]);
                    $assignmentsToKeep[] = $existingAssignment->id;
                    $existingAssignment->load(['class.stream', 'subject']);
                    $createdAssignments[] = $existingAssignment;
                } else {
                    // Create new assignment
                    $assignment = EmployeeClass::create([
                        'employee_id' => $employee->id,
                        'class_id' => $validated['class_id'],
                        'subject_id' => $subjectId,
                        'is_class_teacher' => false,
                        'academic_year_id' => $validated['academic_year_id'],
                    ]);
                    $assignment->load(['class.stream', 'subject']);
                    $createdAssignments[] = $assignment;
                    $assignmentsToKeep[] = $assignment->id;
                }
            }

            // Remove assignments that are no longer needed
            EmployeeClass::where('employee_id', $employee->id)
                ->where('class_id', $validated['class_id'])
                ->where('academic_year_id', $validated['academic_year_id'])
                ->whereNotIn('id', $assignmentsToKeep)
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assignments updated successfully.',
                'assignments' => $createdAssignments,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update assignments: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove class assignment from employee (API endpoint for Vue component)
     * Updated to handle bulk deletion
     */
    public function destroyClassAssignment(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'assignment_ids' => 'required|array',
            'assignment_ids.*' => 'exists:employee_class,id',
            'class_id' => 'required|exists:ranks,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        try {
            // Verify all assignments belong to the employee
            $assignments = EmployeeClass::whereIn('id', $validated['assignment_ids'])
                ->where('employee_id', $employee->id)
                ->get();

            if ($assignments->count() !== count($validated['assignment_ids'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some assignments not found for this employee.',
                ], 404);
            }

            // Delete the assignments
            EmployeeClass::whereIn('id', $validated['assignment_ids'])->delete();

            return response()->json([
                'success' => true,
                'message' => count($validated['assignment_ids']) . ' assignment(s) removed successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove assignments: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get assignments for employee (API endpoint for Vue component)
     */
    public function getEmployeeAssignments(Employee $employee, Request $request): JsonResponse
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id'
        ]);

        $assignments = EmployeeClass::with(['class.stream', 'subject', 'academicYear'])
            ->where('employee_id', $employee->id)
            ->where('academic_year_id', $request->academic_year_id)
            ->get();

        return response()->json([
            'success' => true,
            'assignments' => $assignments,
        ]);
    }

    /**
     * Store subject assignments for employee
     */
    public function storeSubjectAssignments(Request $request, Employee $employee): RedirectResponse
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'assignments' => 'required|array',
            'assignments.*.subject_id' => 'required|exists:subjects,id',
            'assignments.*.class_id' => 'nullable|exists:ranks,id',
        ]);

        try {
            DB::transaction(function () use ($employee, $request) {
                $academicYearId = $request->academic_year_id;

                // Remove existing assignments for this academic year
                $employee->subjects()->wherePivot('academic_year_id', $academicYearId)->detach();

                // Add new assignments
                foreach ($request->assignments as $assignment) {
                    if (!empty($assignment['subject_id'])) {
                        $employee->subjects()->attach($assignment['subject_id'], [
                            'academic_year_id' => $academicYearId,
                            'class_id' => $assignment['class_id'] ?? null,
                        ]);
                    }
                }
            });

            return redirect()->back()->with('success', 'Subjects assigned successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to assign subjects: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove class assignment from employee (Legacy method for old form)
     */
    public function removeClassAssignment(Request $request, Employee $employee): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:ranks,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        try {
            $employee->classes()
                ->wherePivot('class_id', $request->class_id)
                ->wherePivot('academic_year_id', $request->academic_year_id)
                ->detach();

            return response()->json([
                'success' => true,
                'message' => 'Class assignment removed successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove class assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove subject assignment from employee
     */
    public function removeSubjectAssignment(Employee $employee, Request $request): JsonResponse
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        try {
            $employee->subjects()
                ->wherePivot('subject_id', $request->subject_id)
                ->wherePivot('academic_year_id', $request->academic_year_id)
                ->detach();

            return response()->json([
                'success' => true,
                'message' => 'Subject assignment removed successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove subject assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teachers for a specific class
     */
    public function getTeachersForClass(Rank $class, Request $request): JsonResponse
    {
        $request->validate([
            'academic_year_id' => 'nullable|exists:academic_years,id'
        ]);

        $academicYearId = $request->academic_year_id ?? AcademicYear::where('is_active', true)->value('id');

        if (!$academicYearId) {
            return response()->json([
                'success' => false,
                'message' => 'No active academic year found.'
            ], 404);
        }

        $teachers = Employee::whereHas('classes', function ($query) use ($class, $academicYearId) {
            $query->where('class_id', $class->id)
                ->where('academic_year_id', $academicYearId);
        })->with([
            'user',
            'subjects' => function ($query) use ($class, $academicYearId) {
                $query->wherePivot('class_id', $class->id)
                    ->wherePivot('academic_year_id', $academicYearId);
            }
        ])->get();

        return response()->json([
            'success' => true,
            'teachers' => $teachers
        ]);
    }

    /**
     * Get classes assigned to employee for specific academic year
     */
    public function getEmployeeClasses(Employee $employee, Request $request): JsonResponse
    {
        $request->validate([
            'academic_year_id' => 'nullable|exists:academic_years,id'
        ]);

        $academicYearId = $request->academic_year_id ?? AcademicYear::where('is_active', true)->value('id');

        if (!$academicYearId) {
            return response()->json([
                'success' => false,
                'message' => 'No academic year specified or active academic year not found.'
            ], 404);
        }

        $classes = $employee->classes()
            ->wherePivot('academic_year_id', $academicYearId)
            ->withPivot('subject_id', 'is_class_teacher')
            ->with(['subjects'])
            ->get();

        return response()->json([
            'success' => true,
            'classes' => $classes
        ]);
    }

    /**
     * Get employee's assigned subjects
     */
    public function getEmployeeSubjects(Employee $employee): JsonResponse
    {
        $subjects = $employee->subjects()
            ->withPivot('academic_year_id', 'class_id')
            ->get();

        return response()->json(['subjects' => $subjects]);
    }

    /**
     * Get available teachers for class assignment
     */
    public function getAvailableTeachers(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'nullable|exists:ranks,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'exclude_assigned' => 'boolean'
        ]);

        $academicYearId = $request->academic_year_id ?? AcademicYear::where('is_active', true)->value('id');
        $classId = $request->class_id;

        $query = Employee::where('has_system_access', true)
            ->with(['user', 'employmentType']);

        if ($request->exclude_assigned && $classId && $academicYearId) {
            // Exclude teachers already assigned to this class in the academic year
            $query->whereDoesntHave('classes', function ($q) use ($classId, $academicYearId) {
                $q->where('class_id', $classId)
                    ->where('academic_year_id', $academicYearId);
            });
        }

        $teachers = $query->get();

        return response()->json([
            'success' => true,
            'teachers' => $teachers
        ]);
    }

    /**
     * Get available subjects for assignment
     */
    public function getAvailableSubjects(): JsonResponse
    {
        $subjects = Subject::where('activated', true)->orderBy('name')->get();
        return response()->json(['subjects' => $subjects]);
    }

    /**
     * Get teachers for specific subject
     */
    public function getTeachersForSubject(Subject $subject): JsonResponse
    {
        $teachers = $subject->employees()
            ->with('gender', 'employmentType')
            ->get();

        return response()->json(['teachers' => $teachers]);
    }

    /**
     * Get teacher statistics for dashboard
     */
    public function getTeacherStatistics(): JsonResponse
    {
        $totalTeachers = Employee::teachers()->count();
        $activeTeachers = Employee::teachers()->active()->count();
        $currentAcademicYearId = AcademicYear::where('is_active', true)->value('id');

        $teachersWithClasses = $currentAcademicYearId
            ? Employee::whereHas('classes', function ($query) use ($currentAcademicYearId) {
                $query->where('academic_year_id', $currentAcademicYearId);
            })->count()
            : 0;

        return response()->json([
            'success' => true,
            'statistics' => [
                'total_teachers' => $totalTeachers,
                'active_teachers' => $activeTeachers,
                'teachers_with_classes' => $teachersWithClasses,
                'teachers_without_classes' => $activeTeachers - $teachersWithClasses,
                'current_academic_year_id' => $currentAcademicYearId,
            ]
        ]);
    }

    /**
     * Get employee class assignments with detailed information
     */
    public function getEmployeeClassAssignments(Employee $employee, Request $request): JsonResponse
    {
        $request->validate([
            'academic_year_id' => 'nullable|exists:academic_years,id'
        ]);

        $academicYearId = $request->academic_year_id ?? AcademicYear::where('is_active', true)->value('id');

        $assignments = $employee->classes()
            ->wherePivot('academic_year_id', $academicYearId)
            ->withPivot('subject_id', 'is_class_teacher', 'academic_year_id')
            ->with(['subjects'])
            ->get()
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'subject_id' => $class->pivot->subject_id,
                    'subject_name' => $class->subjects->first()?->name ?? 'All Subjects',
                    'is_class_teacher' => (bool) $class->pivot->is_class_teacher,
                    'academic_year_id' => $class->pivot->academic_year_id,
                ];
            });

        return response()->json([
            'success' => true,
            'assignments' => $assignments,
            'academic_year_id' => $academicYearId
        ]);
    }

    // =========================================================================
    // SIMPLIFIED METHODS FOR MARKS ENTRY - LOAD ALL STUDENTS FROM CLASS
    // =========================================================================

    /**
     * Get teacher's assigned subjects for a class (for marks entry)
     */
    public function getTeacherSubjects(Request $request): JsonResponse
    {
        try {
            $employee = Auth::guard('employee')->user();
            $classId = $request->get('class_id');

            if (!$classId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Class ID is required',
                    'data' => []
                ], 400);
            }

            // Get current academic year
            $academicYearId = $request->get('academic_year_id') ?? $this->getCurrentAcademicYearId();

            if (!$academicYearId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active academic year found.',
                    'data' => []
                ], 404);
            }

            // Get subjects assigned to this teacher for the specified class and academic year
            $subjects = EmployeeClass::with('subject')
                ->where('employee_id', $employee->id)
                ->where('class_id', $classId)
                ->where('academic_year_id', $academicYearId)
                ->whereNotNull('subject_id')
                ->get()
                ->map(function ($employeeClass) {
                    return [
                        'id' => $employeeClass->subject_id,
                        'name' => $employeeClass->subject->name,
                        'code' => $employeeClass->subject->code,
                        'max_marks' => $employeeClass->subject->max_marks ?? 100,
                    ];
                })
                ->unique('id')
                ->values();

            return response()->json([
                'success' => true,
                'data' => $subjects
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch teacher subjects: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subjects',
                'error' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get employee classes for authenticated teacher (for marks entry)
     */
    public function getEmployeeClassesForTeacher(Request $request): JsonResponse
    {
        try {
            $employee = Auth::guard('employee')->user();

            $request->validate([
                'academic_year_id' => 'nullable|exists:academic_years,id'
            ]);

            $academicYearId = $request->academic_year_id ?? $this->getCurrentAcademicYearId();

            if (!$academicYearId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No academic year specified or active academic year not found.'
                ], 404);
            }

            // Get classes assigned to this teacher using EmployeeClass model
            $classes = EmployeeClass::with(['class.stream', 'subject'])
                ->where('employee_id', $employee->id)
                ->where('academic_year_id', $academicYearId)
                ->get()
                ->groupBy('class_id')
                ->map(function ($classAssignments) {
                    $class = $classAssignments->first()->class;
                    $subjects = $classAssignments->whereNotNull('subject_id')->pluck('subject');
                    $isClassTeacher = $classAssignments->contains('is_class_teacher', true);

                    return [
                        'id' => $class->id,
                        'name' => $class->name,
                        'stream' => $class->stream ? $class->stream->name : null,
                        'full_name' => $class->stream ? $class->name . ' - ' . $class->stream->name : $class->name,
                        'subjects' => $subjects,
                        'is_class_teacher' => $isClassTeacher,
                        'subject_count' => $subjects->count(),
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'data' => $classes
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch employee classes for teacher: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch classes',
                'error' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get current academic year ID (helper method)
     */
    private function getCurrentAcademicYearId()
    {
        return AcademicYear::where('is_active', true)->value('id');
    }

    /**
     * SIMPLIFIED: Get all students from selected class for marks entry
     */
    public function getEnrolledStudentsForMarks(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'exam_id' => 'required|exists:exams,id',
                'class_id' => 'required|exists:ranks,id',
                'subject_id' => 'required|exists:subjects,id',
            ]);

            // Get authenticated employee
            $employee = Auth::guard('employee')->user();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not authenticated. Please login again.',
                    'data' => []
                ], 401);
            }

            $classId = $request->class_id;

            Log::info("Loading all students for class", [
                'employee_id' => $employee->id,
                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                'class_id' => $classId,
                'exam_id' => $request->exam_id,
                'subject_id' => $request->subject_id
            ]);

            // SIMPLIFIED: Just get all students in this class (using rank_id)
            $students = Student::where('rank_id', $classId)
                ->with(['user', 'rank'])
                ->get()
                ->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'student_id' => $student->admission_number,
                        'first_name' => $student->first_name,
                        'last_name' => $student->last_name,
                        'name' => $student->first_name . ' ' . $student->last_name,
                        'email' => $student->user->email ?? 'No email',
                        'admission_number' => $student->admission_number,
                        'class_name' => $student->rank->name ?? 'Unknown Class',
                    ];
                });

            Log::info("Students loaded successfully", [
                'class_id' => $classId,
                'student_count' => $students->count(),
                'students' => $students->pluck('name')->toArray()
            ]);

            return response()->json([
                'success' => true,
                'data' => $students,
                'count' => $students->count(),
                'message' => 'Loaded ' . $students->count() . ' student(s) from class'
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading students: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading students: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Alternative method to get students for a class (using rank_id) - SIMPLIFIED VERSION
     */
    public function getClassStudents($classId, Request $request): JsonResponse
    {
        try {
            $employee = Auth::guard('employee')->user();

            // Get students in this class using rank_id
            $students = Student::where('rank_id', $classId)
                ->with(['user', 'rank'])
                ->get()
                ->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'student_id' => $student->admission_number,
                        'first_name' => $student->first_name,
                        'last_name' => $student->last_name,
                        'name' => $student->first_name . ' ' . $student->last_name,
                        'email' => $student->user->email ?? 'No email',
                        'admission_number' => $student->admission_number,
                        'class_name' => $student->rank->name ?? 'Unknown',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $students
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch class students: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch students',
                'error' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get teacher's workload summary
     */
    public function getTeacherWorkload(Request $request): JsonResponse
    {
        try {
            $employee = Auth::guard('employee')->user();
            $academicYearId = $request->academic_year_id ?? $this->getCurrentAcademicYearId();

            if (!$academicYearId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active academic year found.'
                ], 404);
            }

            $assignments = EmployeeClass::with(['class', 'subject'])
                ->where('employee_id', $employee->id)
                ->where('academic_year_id', $academicYearId)
                ->get();

            $workload = [
                'total_classes' => $assignments->groupBy('class_id')->count(),
                'total_subjects' => $assignments->whereNotNull('subject_id')->groupBy('subject_id')->count(),
                'class_teacher_count' => $assignments->where('is_class_teacher', true)->count(),
                'subject_teacher_count' => $assignments->whereNotNull('subject_id')->count(),
                'assignments' => $assignments->map(function ($assignment) {
                    return [
                        'class_name' => $assignment->class->name,
                        'stream' => $assignment->class->stream ? $assignment->class->stream->name : null,
                        'subject_name' => $assignment->subject ? $assignment->subject->name : 'All Subjects (Class Teacher)',
                        'is_class_teacher' => $assignment->is_class_teacher,
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'data' => $workload
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch teacher workload: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch workload information',
                'error' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
}
