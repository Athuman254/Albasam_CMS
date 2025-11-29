<?php

namespace App\Services\Timetable;

use App\Models\Timetable\TimetableSubjectAllocation;
use App\Models\User;
use App\Models\Rank;
use App\Models\Timetable\TimetablePeriod;

class TimetableValidatorService
{
    /**
     * Validate data before generation.
     * Returns an array of warnings/errors.
     */
    public function validate(int $academicYearId): array
    {
        $issues = [];

        // 1. Check for Teachers with NO allocations
        // Get all active teachers
        $activeTeachers = User::has('teacher')
            ->where('activated', true)
            ->get();

        foreach ($activeTeachers as $teacher) {
            $hasAllocation = TimetableSubjectAllocation::where('academic_year_id', $academicYearId)
                ->where('teacher_id', $teacher->id)
                ->exists();

            if (!$hasAllocation) {
                $issues[] = [
                    'type' => 'warning',
                    'message' => "Teacher <strong>{$teacher->name}</strong> has no assigned subjects.",
                    'action' => 'Assign subjects',
                    'teacher_id' => $teacher->id,
                    'link_type' => 'teacher_allocation',
                ];
            }
        }

        // 2. Check for Classes with NO allocations
        $activeClasses = Rank::where('activated', true)->get();

        foreach ($activeClasses as $class) {
            $hasAllocation = TimetableSubjectAllocation::where('academic_year_id', $academicYearId)
                ->where('class_id', $class->id)
                ->exists();

            if (!$hasAllocation) {
                $issues[] = [
                    'type' => 'error',
                    'message' => "Class <strong>{$class->name}</strong> has no subjects allocated.",
                    'action' => 'Allocate subjects',
                    'class_id' => $class->id,
                    'link_type' => 'class_allocation',
                ];
            }
        }

        // 3. Check Total Hours vs Available Periods
        // Calculate total available periods per week (TimetablePeriod stores all periods for the week)
        $totalSlots = TimetablePeriod::where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->where('is_break', false)
            ->count();

        if ($totalSlots === 0) {
            $issues[] = [
                'type' => 'error',
                'message' => "No active periods found. Please configure periods first.",
                'action' => 'Setup Periods',
            ];
        } else {
            foreach ($activeClasses as $class) {
                $totalAllocatedHours = TimetableSubjectAllocation::where('academic_year_id', $academicYearId)
                    ->where('class_id', $class->id)
                    ->sum('hours_per_week');

                if ($totalAllocatedHours > $totalSlots) {
                    $issues[] = [
                        'type' => 'error',
                        'message' => "Class <strong>{$class->name}</strong> is overloaded! Allocated: {$totalAllocatedHours} periods, Available: {$totalSlots}.",
                        'action' => 'Reduce allocated hours',
                        'class_id' => $class->id,
                        'link_type' => 'class_allocation',
                    ];
                } elseif ($totalAllocatedHours < ($totalSlots * 0.5)) { // Arbitrary 50% threshold for warning
                    $issues[] = [
                        'type' => 'warning',
                        'message' => "Class <strong>{$class->name}</strong> is underloaded. Allocated: {$totalAllocatedHours} periods, Available: {$totalSlots}.",
                        'action' => 'Allocate more subjects',
                        'class_id' => $class->id,
                        'link_type' => 'class_allocation',
                    ];
                }
            }
        }

        return $issues;
    }
}
