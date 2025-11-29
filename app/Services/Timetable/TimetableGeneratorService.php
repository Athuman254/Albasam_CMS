<?php

namespace App\Services\Timetable;

use App\Models\Timetable\TimetableAllocation;
use App\Models\Timetable\TimetablePeriod;
use App\Models\Timetable\TimetableSubjectAllocation;
use App\Models\Timetable\TimetableVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TimetableGeneratorService
{
    protected $conflictService;

    public function __construct(ConflictDetectionService $conflictService)
    {
        $this->conflictService = $conflictService;
    }

    /**
     * Generate a new timetable version.
     */
    /**
     * Generate a new timetable version.
     */
    public function generate(int $academicYearId, string $name, ?string $description = null): TimetableVersion
    {
        // Increase execution time limit to 5 minutes
        set_time_limit(300);

        // 1. Create a new version
        $version = TimetableVersion::create([
            'academic_year_id' => $academicYearId,
            'version_name' => $name,
            'description' => $description,
            'created_by' => Auth::id(),
            'generation_stats' => [],
        ]);

        // 2. Fetch necessary data
        $periods = TimetablePeriod::where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->orderBy('period_order')
            ->get()
            ->values(); // Ensure sequential keys for array access

        // Eager load relationships to prevent N+1 queries during loop
        $allocations = TimetableSubjectAllocation::where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->with(['teacher', 'subject', 'class'])
            ->orderBy('priority', 'desc') // Schedule high priority first
            ->get();

        $daysOfWeek = config('timetable.days_of_week');

        // 3. Generation Algorithm
        $stats = [
            'total_allocations' => $allocations->sum('hours_per_week'),
            'scheduled' => 0,
            'failed' => 0,
            'conflicts' => [],
        ];

        DB::beginTransaction();

        try {
            // Pre-load allocations into conflict service cache
            $this->conflictService->loadAllocations($version->id);

            // Group allocations by class to handle class-level constraints better
            $allocationsByClass = $allocations->groupBy('class_id');

            foreach ($allocations as $allocation) {
                $hoursNeeded = $allocation->hours_per_week;
                $subjectName = strtolower($allocation->subject->name);

                // Kenyan Structure: Double Lessons for Sciences & Math
                $isScienceOrMath = in_array($subjectName, ['mathematics', 'physics', 'chemistry', 'biology']);
                $doubleLessonsNeeded = $isScienceOrMath ? floor($hoursNeeded / 2) : 0;
                $singleLessonsNeeded = $hoursNeeded - ($doubleLessonsNeeded * 2);

                // Kenyan Structure: Morning Priority for Math & Languages
                $isPriority = in_array($subjectName, ['mathematics', 'english', 'kiswahili']);

                // Schedule Double Lessons First
                for ($i = 0; $i < $doubleLessonsNeeded; $i++) {
                    if (!$this->scheduleBlock($version, $allocation, $periods, $daysOfWeek, 2, $isPriority)) {
                        $stats['failed'] += 2;
                        $stats['conflicts'][] = "Could not schedule DOUBLE lesson for {$allocation->subject->name} in {$allocation->class->name}.";
                    } else {
                        $stats['scheduled'] += 2;
                    }
                }

                // Schedule Single Lessons
                for ($i = 0; $i < $singleLessonsNeeded; $i++) {
                    if (!$this->scheduleBlock($version, $allocation, $periods, $daysOfWeek, 1, $isPriority)) {
                        $stats['failed']++;
                        $stats['conflicts'][] = "Could not schedule single lesson for {$allocation->subject->name} in {$allocation->class->name}.";
                    } else {
                        $stats['scheduled']++;
                    }
                }
            }

            $version->update(['generation_stats' => $stats]);
            DB::commit();

            return $version;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Timetable generation failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Attempt to schedule a block of lessons (1 or 2 periods).
     */
    protected function scheduleBlock(TimetableVersion $version, TimetableSubjectAllocation $allocation, $periods, $daysOfWeek, int $duration, bool $isPriority): bool
    {
        // Shuffle days to distribute load
        $shuffledDays = $daysOfWeek;
        shuffle($shuffledDays);

        // Iterate through shuffled days
        foreach ($shuffledDays as $day) {
            // Filter periods for the current day
            $dayPeriods = $periods->where('day_of_week', $day)->values();

            // Iterate through periods for this day
            foreach ($dayPeriods as $index => $period) {
                // Skip if period is a break
                if ($period->is_break) {
                    continue;
                }

                // Priority Logic: If priority subject, try to stick to morning periods (e.g., first 4)
                if ($isPriority && $period->period_order > 4) {
                    // Skip if we are in strict priority mode, OR just continue to find a slot later if strict fails?
                    // For now, let's just prefer morning but allow afternoon if full.
                    // To implement strict preference, we could do two passes: 1. Morning only, 2. Any time.
                }

                // Check if we have enough remaining periods in the day
                if ($index + $duration > count($dayPeriods)) {
                    continue;
                }

                // Check for breaks (if any) - assuming periods are contiguous blocks
                // In a real scenario, we might need to check if periods[$index] and periods[$index+1] are actually adjacent in time

                // Check if we can schedule starting at this period
                $canSchedule = true;
                for ($d = 0; $d < $duration; $d++) {
                    $currentPeriod = $dayPeriods[$index + $d];

                    // Check conflicts
                    $conflicts = $this->conflictService->checkConflicts(
                        $version->id,
                        $currentPeriod->id,
                        $day,
                        $allocation->class_id,
                        $allocation->teacher_id,
                        null // Room ID (optional)
                    );

                    if (!empty($conflicts)) {
                        $canSchedule = false;
                        break;
                    }
                }

                if ($canSchedule) {
                    // Create allocations
                    for ($d = 0; $d < $duration; $d++) {
                        $currentPeriod = $dayPeriods[$index + $d];
                        $newAllocation = TimetableAllocation::create([
                            'version_id' => $version->id,
                            'period_id' => $currentPeriod->id,
                            'teacher_id' => $allocation->teacher_id,
                            'subject_id' => $allocation->subject_id,
                            'class_id' => $allocation->class_id,
                            'academic_year_id' => $version->academic_year_id,
                            'day_of_week' => $day,
                            'period_number' => $currentPeriod->period_order,
                            'start_time' => $currentPeriod->start_time,
                            'end_time' => $currentPeriod->end_time,
                            'status' => 'scheduled',
                        ]);

                        // Update cache to prevent double bookings in same generation run
                        $this->conflictService->addAllocationToCache($newAllocation);
                    }
                    return true;
                }
            }
        }
        return false;
    }
}
