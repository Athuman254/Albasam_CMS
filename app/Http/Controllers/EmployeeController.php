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
    public function index(): Response
    {
        return Inertia::render('Admin/Employees/Index', [
            'employees' => Employee::with([
                'employmentType',
                'employmentStatus', 
                'gender',
                'classes'
            ])->latest()->paginate(20),
            'filters' => request()->all(['search', 'trashed']),
        ]);
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Employees/Create', [
            // Add any necessary data for the create form
        ]);
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'staff_number' => 'required|string|unique:employees',
            // Add other validation rules
        ]);

        $employee = Employee::create($validated);

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee created successfully.');
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
            ->when($currentAcademicYear, function($query) use ($currentAcademicYear) {
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
        return Inertia::render('Admin/Employees/Edit', [
            'employee' => $employee->load([
                'user',
                'employmentType',
                'employmentStatus',
                'gender'
            ]),
        ]);
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'staff_number' => 'required|string|unique:employees,staff_number,' . $employee->id,
            // Add other validation rules
        ]);

        $employee->update($validated);

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully.');
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
                'classes' => function($query) {
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
            DB::table('employee_class')
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
            AllowedFilter::scope('search', function ($query, $value) {
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
            ->when($currentAcademicYear, function($query) use ($currentAcademicYear) {
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
            'subjects' => function($query) use ($class, $academicYearId) {
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
            ? Employee::whereHas('classes', function($query) use ($currentAcademicYearId) {
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