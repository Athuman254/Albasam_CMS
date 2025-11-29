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
    protected $allocationsCache = [];

    /**
     * Pre-fetch allocations for a version to optimize conflict checking.
     */
    public function loadAllocations(int $versionId)
    {
        $this->allocationsCache[$versionId] = TimetableAllocation::where('version_id', $versionId)
            ->with(['class', 'subject']) // Eager load for conflict messages
            ->get();
    }

    /**
     * Add a newly created allocation to the cache.
     */
    public function addAllocationToCache(TimetableAllocation $allocation)
    {
        if (!isset($this->allocationsCache[$allocation->version_id])) {
            $this->allocationsCache[$allocation->version_id] = collect([]);
        }
        $this->allocationsCache[$allocation->version_id]->push($allocation);
    }

    /**
     * Check for conflicts for a proposed allocation.
     */
    public function checkConflicts(int $versionId, int $periodId, string $dayOfWeek, int $classId, ?int $teacherId, ?int $roomId): array
    {
        $conflicts = [];

        // Use cached allocations if available, otherwise fetch (fallback)
        $allocations = $this->allocationsCache[$versionId] ?? TimetableAllocation::where('version_id', $versionId)->get();

        // Filter relevant allocations from memory
        $dayAllocations = $allocations->filter(function ($a) use ($periodId, $dayOfWeek) {
            return $a->period_id == $periodId && $a->day_of_week == $dayOfWeek;
        });

        // 1. Teacher Conflict: Is the teacher already teaching in this period?
        if ($teacherId) {
            $teacherConflict = $dayAllocations->firstWhere('teacher_id', $teacherId);

            if ($teacherConflict) {
                $conflicts[] = [
                    'type' => 'teacher_double_booking',
                    'message' => "Teacher is already assigned to {$teacherConflict->class->name} in this period.",
                    'conflicting_allocation_id' => $teacherConflict->id,
                ];
            }
        }

        // 2. Class Conflict: Does the class already have a lesson in this period?
        $classConflict = $dayAllocations->firstWhere('class_id', $classId);

        if ($classConflict) {
            $conflicts[] = [
                'type' => 'class_double_booking',
                'message' => "Class already has a lesson ({$classConflict->subject->name}) in this period.",
                'conflicting_allocation_id' => $classConflict->id,
            ];
        }

        // 3. Room Conflict: Is the room already occupied?
        if ($roomId) {
            $roomConflict = $dayAllocations->firstWhere('room_id', $roomId);

            if ($roomConflict) {
                $conflicts[] = [
                    'type' => 'room_double_booking',
                    'message' => "Room is already occupied by {$roomConflict->class->name} in this period.",
                    'conflicting_allocation_id' => $roomConflict->id,
                ];
            }
        }

        // 4. Constraint Checks
        $constraints = $this->checkConstraints($versionId, $periodId, $dayOfWeek, $classId, $teacherId, $roomId);

        // Log if constraints found to debug 0% coverage
        if (!empty($constraints)) {
            \Illuminate\Support\Facades\Log::info("Constraint Conflict: " . json_encode($constraints));
        }

        $conflicts = array_merge($conflicts, $constraints);

        return $conflicts;
    }

    /**
     * Check constraints for a proposed allocation.
     */
    protected function checkConstraints(int $versionId, int $periodId, string $dayOfWeek, int $classId, ?int $teacherId, ?int $roomId): array
    {
        $conflicts = [];

        // 1. Teacher Constraints
        if ($teacherId) {
            $teacherConstraint = TimetableConstraint::where('constraint_type', 'teacher_availability')
                ->where('teacher_id', $teacherId)
                ->where(function ($query) use ($dayOfWeek, $periodId) {
                    // Check for specific day AND (specific period OR all periods)
                    $query->where('day_of_week', $dayOfWeek)
                        ->where(function ($q) use ($periodId) {
                            $q->where('period_number', $periodId) // Assuming period_number matches period_id or order
                                ->orWhereNull('period_number');
                        });
                })
                ->first();

            if ($teacherConstraint) {
                if ($teacherConstraint->constraint_value === 'unavailable') {
                    $conflicts[] = [
                        'type' => 'teacher_unavailable',
                        'message' => "Teacher is marked as UNAVAILABLE for this time slot.",
                    ];
                }
                // We can handle 'preferred' logic in the generator scoring system later
            }
        }

        // 2. Room Constraints
        if ($roomId) {
            $roomConstraint = TimetableConstraint::where('constraint_type', 'room_availability')
                ->where('room_id', $roomId)
                ->where(function ($query) use ($dayOfWeek, $periodId) {
                    $query->where('day_of_week', $dayOfWeek)
                        ->where(function ($q) use ($periodId) {
                            $q->where('period_number', $periodId)
                                ->orWhereNull('period_number');
                        });
                })
                ->first();

            if ($roomConstraint && $roomConstraint->constraint_value === 'unavailable') {
                $conflicts[] = [
                    'type' => 'room_unavailable',
                    'message' => "Room is marked as UNAVAILABLE for this time slot.",
                ];
            }
        }

        // 3. Max Consecutive Lessons Constraint
        if ($teacherId) {
            $consecutiveConstraint = TimetableConstraint::where('constraint_type', 'consecutive_periods')
                ->where('teacher_id', $teacherId)
                ->first();

            if ($consecutiveConstraint) {
                $limit = (int) $consecutiveConstraint->constraint_value;
                if ($this->wouldExceedConsecutiveLimit($versionId, $teacherId, $dayOfWeek, $periodId, $limit)) {
                    $conflicts[] = [
                        'type' => 'consecutive_periods',
                        'message' => "Teacher would exceed limit of {$limit} consecutive lessons.",
                    ];
                }
            }
        }

        return $conflicts;
    }

    /**
     * Check if adding a period would exceed consecutive lesson limit.
     */
    protected function wouldExceedConsecutiveLimit($versionId, $teacherId, $dayOfWeek, $periodId, $limit)
    {
        // Get current allocations from cache if available, otherwise fallback to DB
        $allocations = $this->allocationsCache[$versionId] ?? TimetableAllocation::where('version_id', $versionId)->get();

        // Filter for this teacher and day
        $teacherAllocations = $allocations->filter(function ($a) use ($teacherId, $dayOfWeek) {
            return $a->teacher_id == $teacherId && $a->day_of_week == $dayOfWeek;
        });

        // Get period orders
        // Note: We need to ensure 'period' relation is loaded or available. 
        // If using cache from loadAllocations, it is eager loaded.
        // If using fallback, we might need to load it.
        $orders = $teacherAllocations->map(function ($a) {
            return $a->period ? $a->period->period_order : $a->period_number;
        })->toArray();

        // Get proposed period order
        $proposedPeriod = TimetablePeriod::find($periodId);
        if (!$proposedPeriod) return false;

        $orders[] = $proposedPeriod->period_order;
        sort($orders);
        $orders = array_unique($orders); // Handle potential duplicates

        // Check consecutive
        $consecutive = 0;
        $lastOrder = -100;

        foreach ($orders as $order) {
            if ($order == $lastOrder + 1) {
                $consecutive++;
            } else {
                $consecutive = 1;
            }

            if ($consecutive > $limit) {
                return true;
            }
            $lastOrder = $order;
        }

        return false;
    }
}
