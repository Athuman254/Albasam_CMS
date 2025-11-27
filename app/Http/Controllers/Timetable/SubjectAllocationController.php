<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Models\Settings\AcademicYear;
use App\Models\Rank;
use App\Models\Subject;
use App\Models\Timetable\TimetableSubjectAllocation;
use App\Models\User;
use App\Services\Timetable\WorkloadCalculatorService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubjectAllocationController extends Controller
{
    protected $workloadCalculator;

    public function __construct(WorkloadCalculatorService $workloadCalculator)
    {
        $this->workloadCalculator = $workloadCalculator;
    }

    /**
     * Display the subject allocation page.
     */
    public function index(Request $request)
    {
        $academicYearId = $request->input('academic_year_id', AcademicYear::where('is_active', true)->first()?->id);

        $allocations = TimetableSubjectAllocation::with(['teacher', 'subject', 'class', 'academicYear'])
            ->where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->orderBy('teacher_id')
            ->get()
            ->groupBy('teacher.name');

        $teachers = User::has('teacher')
            ->where('activated', true)
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get()
            ->map(function ($teacher) {
                $teacher->full_name = $teacher->name; // Add full_name attribute
                return $teacher;
            });

        $subjects = Subject::where('activated', 1)->orderBy('name')->get();
        $classes = Rank::where('activated', 1)->orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        // Get workload for all teachers
        $teacherWorkloads = $this->workloadCalculator->calculateAllTeacherWorkloads($academicYearId);

        return Inertia::render('Timetable/Setup/SubjectAllocation', [
            'allocations' => $allocations,
            'teachers' => $teachers,
            'subjects' => $subjects,
            'classes' => $classes,
            'academicYears' => $academicYears,
            'currentAcademicYearId' => $academicYearId,
            'teacherWorkloads' => $teacherWorkloads,
            'workloadLimits' => config('timetable.limits'),
        ]);
    }

    /**
     * Store new allocations (supports per-subject allocation).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'teacher_id' => 'required|exists:users,id',
            'allocations' => 'required|array|min:1',
            'allocations.*.subject_id' => 'required|exists:subjects,id',
            'allocations.*.class_ids' => 'required|array|min:1',
            'allocations.*.class_ids.*' => 'exists:ranks,id',
            'allocations.*.hours_per_week' => 'required|integer|min:1|max:10',
            'allocations.*.priority' => 'in:high,medium,low',
        ]);

        // Calculate total workload for validation
        $totalNewHours = 0;
        $uniqueSubjects = [];
        $uniqueClasses = [];

        foreach ($validated['allocations'] as $allocation) {
            $totalNewHours += count($allocation['class_ids']) * $allocation['hours_per_week'];
            $uniqueSubjects[$allocation['subject_id']] = true;
            foreach ($allocation['class_ids'] as $classId) {
                $uniqueClasses[$classId] = true;
            }
        }

        // Check workload before creating
        $overloadCheck = $this->workloadCalculator->checkTeacherOverload(
            $validated['teacher_id'],
            $validated['academic_year_id'],
            count($uniqueSubjects),
            count($uniqueClasses),
            $totalNewHours
        );

        if ($overloadCheck['is_overloaded']) {
            return back()->withErrors([
                'workload' => 'Teacher workload would exceed limits: ' . implode(', ', $overloadCheck['warnings'])
            ]);
        }

        $created = 0;
        $existing = 0;

        // Create allocations for each subject-class combination
        foreach ($validated['allocations'] as $allocation) {
            foreach ($allocation['class_ids'] as $classId) {
                // Check if allocation already exists
                $exists = TimetableSubjectAllocation::where([
                    'academic_year_id' => $validated['academic_year_id'],
                    'teacher_id' => $validated['teacher_id'],
                    'subject_id' => $allocation['subject_id'],
                    'class_id' => $classId,
                ])->exists();

                if ($exists) {
                    $existing++;
                    continue;
                }

                TimetableSubjectAllocation::create([
                    'academic_year_id' => $validated['academic_year_id'],
                    'teacher_id' => $validated['teacher_id'],
                    'subject_id' => $allocation['subject_id'],
                    'class_id' => $classId,
                    'hours_per_week' => $allocation['hours_per_week'],
                    'priority' => $allocation['priority'] ?? 'medium',
                    'status' => 'active',
                    'created_by' => auth()->id(),
                ]);

                $created++;
            }
        }

        // Recalculate workload
        $this->workloadCalculator->recalculateTeacherWorkload($validated['teacher_id'], $validated['academic_year_id']);

        $message = "Created {$created} allocation(s).";
        if ($existing > 0) {
            $message .= " {$existing} allocation(s) already existed.";
        }

        return back()->with('success', $message);
    }

    /**
     * Delete an allocation.
     */
    public function destroy(TimetableSubjectAllocation $allocation)
    {
        $teacherId = $allocation->teacher_id;
        $academicYearId = $allocation->academic_year_id;

        $allocation->delete();

        // Recalculate workload
        $this->workloadCalculator->recalculateTeacherWorkload($teacherId, $academicYearId);

        return back()->with('success', 'Allocation deleted successfully.');
    }

    /**
     * Bulk delete allocations.
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'allocation_ids' => 'required|array|min:1',
            'allocation_ids.*' => 'exists:timetable_subject_allocations,id',
        ]);

        $allocations = TimetableSubjectAllocation::whereIn('id', $validated['allocation_ids'])->get();
        $affectedTeachers = $allocations->pluck('teacher_id')->unique();
        $academicYearId = $allocations->first()->academic_year_id;

        TimetableSubjectAllocation::whereIn('id', $validated['allocation_ids'])->delete();

        // Recalculate workload for affected teachers
        foreach ($affectedTeachers as $teacherId) {
            $this->workloadCalculator->recalculateTeacherWorkload($teacherId, $academicYearId);
        }

        return back()->with('success', count($validated['allocation_ids']) . ' allocation(s) deleted successfully.');
    }

    /**
     * Get teacher's current workload (AJAX).
     */
    public function getTeacherWorkload(Request $request, User $teacher)
    {
        $academicYearId = $request->input('academic_year_id');

        $workload = $this->workloadCalculator->calculateTeacherWorkload($teacher->id, $academicYearId);

        return response()->json($workload);
    }

    /**
     * Get subjects that a teacher is qualified to teach.
     */
    public function getTeacherSubjects(User $teacher)
    {
        $subjects = $teacher->subjects()->where('activated', 1)->get();

        return response()->json([
            'subjects' => $subjects,
            'has_subjects' => $subjects->isNotEmpty(),
        ]);
    }

    /**
     * Get teacher workload status with warnings.
     */
    public function getTeacherWorkloadStatus(Request $request, User $teacher)
    {
        $academicYearId = $request->input('academic_year_id');
        $workload = $this->workloadCalculator->calculateTeacherWorkload($teacher->id, $academicYearId);
        $limits = config('timetable.limits');

        $hoursPercentage = ($workload['total_hours_per_week'] / $limits['max_hours_per_week']) * 100;
        $classesPercentage = ($workload['total_classes'] / $limits['max_classes_per_teacher']) * 100;
        $subjectsPercentage = ($workload['total_subjects'] / $limits['max_subjects_per_teacher']) * 100;

        $status = 'available';
        $warnings = [];

        if ($hoursPercentage >= 100 || $classesPercentage >= 100 || $subjectsPercentage >= 100) {
            $status = 'at_limit';
            $warnings[] = 'Teacher has reached allocation limit';
        } elseif ($hoursPercentage >= 80 || $classesPercentage >= 80 || $subjectsPercentage >= 80) {
            $status = 'near_limit';
            $warnings[] = 'Teacher is near allocation limit';
        }

        return response()->json([
            'status' => $status,
            'workload' => $workload,
            'limits' => $limits,
            'percentages' => [
                'hours' => round($hoursPercentage, 1),
                'classes' => round($classesPercentage, 1),
                'subjects' => round($subjectsPercentage, 1),
            ],
            'warnings' => $warnings,
            'can_allocate' => $status !== 'at_limit',
        ]);
    }

    /**
     * Suggest alternative teachers with same subject combination.
     */
    public function suggestAlternativeTeachers(Request $request)
    {
        $subjectId = $request->input('subject_id');
        $excludeTeacherId = $request->input('exclude_teacher_id');
        $academicYearId = $request->input('academic_year_id');

        // Find teachers who teach this subject
        $teachers = User::whereHas('roles', function ($query) {
            $query->where('name', 'Teacher');
        })
            ->whereHas('subjects', function ($query) use ($subjectId) {
                $query->where('subjects.id', $subjectId);
            })
            ->where('id', '!=', $excludeTeacherId)
            ->get();

        $suggestions = [];
        foreach ($teachers as $teacher) {
            $workloadStatus = $this->getTeacherWorkloadStatus(
                new Request(['academic_year_id' => $academicYearId]),
                $teacher
            )->getData();

            if ($workloadStatus->can_allocate) {
                $suggestions[] = [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'workload' => $workloadStatus->workload,
                    'status' => $workloadStatus->status,
                ];
            }
        }

        return response()->json([
            'suggestions' => $suggestions,
            'count' => count($suggestions),
        ]);
    }

    /**
     * Analyze class coverage - detect classes missing required allocations.
     */
    public function analyzeClassCoverage(Request $request)
    {
        $academicYearId = $request->input('academic_year_id');

        $classes = Rank::where('activated', 1)->get();
        $requiredSubjects = Subject::where('activated', 1)
            ->where('is_core', true) // Assuming core subjects are required
            ->get();

        $coverage = [];
        foreach ($classes as $class) {
            $allocatedSubjects = TimetableSubjectAllocation::where('academic_year_id', $academicYearId)
                ->where('class_id', $class->id)
                ->where('status', 'active')
                ->pluck('subject_id')
                ->unique();

            $missingSubjects = $requiredSubjects->whereNotIn('id', $allocatedSubjects);

            $coverage[] = [
                'class_id' => $class->id,
                'class_name' => $class->name,
                'total_allocations' => $allocatedSubjects->count(),
                'required_subjects' => $requiredSubjects->count(),
                'missing_subjects' => $missingSubjects->values(),
                'coverage_percentage' => $requiredSubjects->count() > 0
                    ? round(($allocatedSubjects->count() / $requiredSubjects->count()) * 100, 1)
                    : 0,
                'is_complete' => $missingSubjects->isEmpty(),
            ];
        }

        return response()->json([
            'coverage' => $coverage,
            'classes_with_gaps' => collect($coverage)->where('is_complete', false)->count(),
        ]);
    }

    /**
     * Update an existing allocation.
     */
    public function update(Request $request, TimetableSubjectAllocation $allocation)
    {
        $validated = $request->validate([
            'hours_per_week' => 'required|integer|min:1|max:10',
            'priority' => 'in:high,medium,low',
        ]);

        // Check if new hours would cause overload
        $currentHours = $allocation->hours_per_week;
        $newHours = $validated['hours_per_week'];
        $hoursDifference = $newHours - $currentHours;

        if ($hoursDifference > 0) {
            $overloadCheck = $this->workloadCalculator->checkTeacherOverload(
                $allocation->teacher_id,
                $allocation->academic_year_id,
                0, // No new subjects
                0, // No new classes
                $hoursDifference // Additional hours
            );

            if ($overloadCheck['is_overloaded']) {
                return back()->withErrors([
                    'workload' => 'Updated hours would exceed teacher workload limits: ' . implode(', ', $overloadCheck['warnings'])
                ]);
            }
        }

        $allocation->update($validated);

        // Recalculate workload
        $this->workloadCalculator->recalculateTeacherWorkload(
            $allocation->teacher_id,
            $allocation->academic_year_id
        );

        return back()->with('success', 'Allocation updated successfully.');
    }
}
