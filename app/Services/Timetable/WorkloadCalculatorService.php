<?php

namespace App\Services\Timetable;

use App\Models\Timetable\TimetableSubjectAllocation;
use App\Models\Timetable\TimetableTeacherWorkload;
use Illuminate\Support\Facades\DB;

class WorkloadCalculatorService
{
    /**
     * Calculate and cache workload for a specific teacher.
     */
    public function calculateTeacherWorkload(int $teacherId, int $academicYearId): TimetableTeacherWorkload
    {
        $allocations = TimetableSubjectAllocation::where('teacher_id', $teacherId)
            ->where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->get();

        $totalClasses = $allocations->unique('class_id')->count();
        $totalSubjects = $allocations->unique('subject_id')->count();
        $totalHours = $allocations->sum('hours_per_week');

        // Calculate average classes per day (assuming 5 days week for calculation base)
        // This is a rough estimate based on allocated hours / typical lesson duration
        // A more accurate one would require the actual generated timetable
        $classesPerDayAvg = $totalHours > 0 ? round($totalHours / 5, 2) : 0;

        return TimetableTeacherWorkload::updateOrCreate(
            [
                'teacher_id' => $teacherId,
                'academic_year_id' => $academicYearId,
            ],
            [
                'total_classes' => $totalClasses,
                'total_subjects' => $totalSubjects,
                'total_hours_per_week' => $totalHours,
                'classes_per_day_avg' => $classesPerDayAvg,
                'last_calculated_at' => now(),
            ]
        );
    }

    /**
     * Calculate workload for all teachers in an academic year.
     */
    public function calculateAllTeacherWorkloads(int $academicYearId)
    {
        $teacherIds = TimetableSubjectAllocation::where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->distinct()
            ->pluck('teacher_id');

        foreach ($teacherIds as $teacherId) {
            $this->calculateTeacherWorkload($teacherId, $academicYearId);
        }

        $limits = config('timetable.limits');

        return TimetableTeacherWorkload::where('academic_year_id', $academicYearId)
            ->with('teacher')
            ->get()
            ->map(function ($workload) use ($limits) {
                return [
                    'teacher_id' => $workload->teacher_id,
                    'total_hours_per_week' => $workload->total_hours_per_week,
                    'total_classes' => $workload->total_classes,
                    'total_subjects' => $workload->total_subjects,
                    'classes_per_day_avg' => $workload->classes_per_day_avg,
                    'max_hours' => $limits['max_hours_per_week'],
                    'max_classes' => $limits['max_classes_per_teacher'],
                    'max_subjects' => $limits['max_subjects_per_teacher'],
                ];
            });
    }

    /**
     * Check if adding new allocations would overload the teacher.
     */
    public function checkTeacherOverload(int $teacherId, int $academicYearId, int $newSubjectsCount, int $newClassesCount, int $newHours): array
    {
        $currentWorkload = $this->calculateTeacherWorkload($teacherId, $academicYearId);
        $limits = config('timetable.limits');

        $projectedClasses = $currentWorkload->total_classes + $newClassesCount;
        $projectedSubjects = $currentWorkload->total_subjects + $newSubjectsCount;
        $projectedHours = $currentWorkload->total_hours_per_week + $newHours;

        $warnings = [];
        $isOverloaded = false;

        if ($projectedClasses > $limits['max_classes_per_teacher']) {
            $warnings[] = "Classes limit exceeded ({$projectedClasses}/{$limits['max_classes_per_teacher']})";
            $isOverloaded = true;
        }

        if ($projectedSubjects > $limits['max_subjects_per_teacher']) {
            $warnings[] = "Subjects limit exceeded ({$projectedSubjects}/{$limits['max_subjects_per_teacher']})";
            $isOverloaded = true;
        }

        if ($projectedHours > $limits['max_hours_per_week']) {
            $warnings[] = "Hours limit exceeded ({$projectedHours}/{$limits['max_hours_per_week']})";
            $isOverloaded = true;
        }

        return [
            'is_overloaded' => $isOverloaded,
            'warnings' => $warnings,
            'current' => $currentWorkload,
            'projected' => [
                'classes' => $projectedClasses,
                'subjects' => $projectedSubjects,
                'hours' => $projectedHours,
            ]
        ];
    }

    /**
     * Force recalculation of a teacher's workload.
     */
    public function recalculateTeacherWorkload(int $teacherId, int $academicYearId): void
    {
        $this->calculateTeacherWorkload($teacherId, $academicYearId);
    }
}
