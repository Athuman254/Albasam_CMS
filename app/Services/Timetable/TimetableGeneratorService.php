<?php

namespace App\Services\Timetable;

use App\Models\Timetable\TimetableAllocation;
use App\Models\Timetable\TimetablePeriod;
use App\Models\Timetable\TimetableSubjectAllocation;
use App\Models\Timetable\TimetableVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    public function generate(int $academicYearId, string $name, ?string $description = null): TimetableVersion
    {
        // 1. Create a new version
        $version = TimetableVersion::create([
            'academic_year_id' => $academicYearId,
            'version_name' => $name,
            'description' => $description,
            'created_by' => auth()->id(),
            'generation_stats' => [],
        ]);

        // 2. Fetch necessary data
        $periods = TimetablePeriod::where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->orderBy('period_order')
            ->get();

        $allocations = TimetableSubjectAllocation::where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->with(['teacher', 'subject', 'class'])
            ->orderBy('priority', 'desc') // Schedule high priority first
            ->get();

        $daysOfWeek = config('timetable.days_of_week');

        // 3. Generation Algorithm (Simplified Greedy Approach)
        $stats = [
            'total_allocations' => $allocations->sum('hours_per_week'),
            'scheduled' => 0,
            'failed' => 0,
            'conflicts' => [],
        ];

        DB::beginTransaction();

        try {
            foreach ($allocations as $allocation) {
                $hoursNeeded = $allocation->hours_per_week;
                $hoursScheduled = 0;

                // Try to schedule each hour
                for ($i = 0; $i < $hoursNeeded; $i++) {
                    $scheduled = false;

                    // Iterate through days and periods to find a slot
                    // Randomize days/periods to distribute load? Or sequential?
                    // Let's try sequential for now, but maybe shuffle days to avoid Monday-heavy schedules
                    $shuffledDays = $daysOfWeek;
                    shuffle($shuffledDays);

                    foreach ($shuffledDays as $day) {
                        foreach ($periods as $period) {
                            if ($period->is_break) continue;

                            // Check conflicts
                            $conflicts = $this->conflictService->checkConflicts(
                                $version->id,
                                $period->id,
                                $day, // Assuming day is string enum, might need mapping if int
                                $allocation->class_id,
                                $allocation->teacher_id,
                                null // Room not handled yet
                            );

                            if (empty($conflicts)) {
                                // Schedule it!
                                TimetableAllocation::create([
                                    'version_id' => $version->id,
                                    'period_id' => $period->id,
                                    'teacher_id' => $allocation->teacher_id,
                                    'subject_id' => $allocation->subject_id,
                                    'class_id' => $allocation->class_id,
                                    'academic_year_id' => $academicYearId,
                                    'day_of_week' => $day,
                                    'period_number' => $period->period_order, // Assuming order is number
                                    'start_time' => $period->start_time,
                                    'end_time' => $period->end_time,
                                    'status' => 'scheduled',
                                ]);

                                $hoursScheduled++;
                                $stats['scheduled']++;
                                $scheduled = true;
                                break 2; // Break out of day and period loops
                            }
                        }
                    }

                    if (!$scheduled) {
                        $stats['failed']++;
                        $stats['conflicts'][] = "Could not schedule {$allocation->subject->name} for {$allocation->class->name} (Teacher: {$allocation->teacher->full_name})";
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
}
