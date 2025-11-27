<?php

namespace App\Services\Timetable;

use App\Models\Timetable\TimetableAllocation;
use App\Models\Timetable\TimetableConstraint;
use App\Models\Timetable\TimetablePeriod;

class ConflictDetectionService
{
    /**
     * Check for conflicts for a proposed allocation.
     */
    public function checkConflicts(int $versionId, int $periodId, int $dayOfWeek, int $classId, ?int $teacherId, ?int $roomId): array
    {
        $conflicts = [];

        // 1. Teacher Conflict: Is the teacher already teaching in this period?
        if ($teacherId) {
            $teacherConflict = TimetableAllocation::where('version_id', $versionId)
                ->where('period_id', $periodId)
                ->where('day_of_week', $dayOfWeek)
                ->where('teacher_id', $teacherId)
                ->first();

            if ($teacherConflict) {
                $conflicts[] = [
                    'type' => 'teacher_double_booking',
                    'message' => "Teacher is already assigned to {$teacherConflict->class->name} in this period.",
                    'conflicting_allocation_id' => $teacherConflict->id,
                ];
            }
        }

        // 2. Class Conflict: Does the class already have a lesson in this period?
        $classConflict = TimetableAllocation::where('version_id', $versionId)
            ->where('period_id', $periodId)
            ->where('day_of_week', $dayOfWeek)
            ->where('class_id', $classId)
            ->first();

        if ($classConflict) {
            $conflicts[] = [
                'type' => 'class_double_booking',
                'message' => "Class already has a lesson ({$classConflict->subject->name}) in this period.",
                'conflicting_allocation_id' => $classConflict->id,
            ];
        }

        // 3. Room Conflict: Is the room already occupied?
        if ($roomId) {
            $roomConflict = TimetableAllocation::where('version_id', $versionId)
                ->where('period_id', $periodId)
                ->where('day_of_week', $dayOfWeek)
                ->where('room_id', $roomId)
                ->first();

            if ($roomConflict) {
                $conflicts[] = [
                    'type' => 'room_double_booking',
                    'message' => "Room is already occupied by {$roomConflict->class->name} in this period.",
                    'conflicting_allocation_id' => $roomConflict->id,
                ];
            }
        }

        // 4. Constraint Checks
        $constraints = $this->checkConstraints($periodId, $dayOfWeek, $classId, $teacherId, $roomId);
        $conflicts = array_merge($conflicts, $constraints);

        return $conflicts;
    }

    /**
     * Check constraints for a proposed allocation.
     */
    protected function checkConstraints(int $periodId, int $dayOfWeek, int $classId, ?int $teacherId, ?int $roomId): array
    {
        $conflicts = [];

        // Fetch relevant constraints
        // This is a simplified check. Real implementation would need to query the constraints table efficiently.
        // For now, we'll assume we can fetch relevant constraints.

        if ($teacherId) {
            $teacherUnavailable = TimetableConstraint::where('constraint_type', 'teacher_availability')
                ->where('teacher_id', $teacherId)
                ->where('day_of_week', $dayOfWeek)
                // ->where('period_id', $periodId) // Assuming period_number maps to period_id or we join tables
                ->where('constraint_value', 'unavailable')
                ->exists();

            if ($teacherUnavailable) {
                $conflicts[] = [
                    'type' => 'teacher_unavailable',
                    'message' => "Teacher is marked as unavailable for this time slot.",
                ];
            }
        }

        return $conflicts;
    }
}
