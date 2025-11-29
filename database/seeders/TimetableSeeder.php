<?php

namespace Database\Seeders;

use App\Models\Settings\AcademicYear;
use App\Models\Rank;
use App\Models\Subject;
use App\Models\Timetable\TimetableConstraint;
use App\Models\Timetable\TimetablePeriod;
use App\Models\Timetable\TimetableRoom;
use App\Models\Timetable\TimetableSubjectAllocation;
use App\Models\User;
use App\Services\Timetable\WorkloadCalculatorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TimetableSeeder extends Seeder
{
    public function run()
    {
        $academicYear = AcademicYear::where('is_active', true)->first();

        if (!$academicYear) {
            $this->command->error('No current academic year found. Please seed academic years first.');
            return;
        }

        $this->command->info("Seeding timetable data for Academic Year: {$academicYear->name}");

        DB::transaction(function () use ($academicYear) {
            $this->seedPeriods($academicYear);
            $this->seedRooms();
            $this->seedAllocations($academicYear);
            $this->seedConstraints($academicYear);
        });

        $this->command->info('Timetable seeding completed successfully.');
    }

    protected function seedPeriods($academicYear)
    {
        // Clear existing periods for this year
        TimetablePeriod::where('academic_year_id', $academicYear->id)->delete();

        $days = config('timetable.days_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);

        // Standard Schedule: 8:00 - 16:00
        // 8:00 - 8:40 (P1)
        // 8:40 - 9:20 (P2)
        // 9:20 - 10:00 (P3)
        // 10:00 - 10:20 (Break)
        // 10:20 - 11:00 (P4)
        // 11:00 - 11:40 (P5)
        // 11:40 - 12:20 (P6)
        // 12:20 - 13:20 (Lunch)
        // 13:20 - 14:00 (P7)
        // 14:00 - 14:40 (P8)
        // 14:40 - 15:20 (P9)
        // 15:20 - 16:00 (Games/Clubs)

        $schedule = [
            ['name' => 'Period 1', 'start' => '08:00', 'end' => '08:40', 'is_break' => false],
            ['name' => 'Period 2', 'start' => '08:40', 'end' => '09:20', 'is_break' => false],
            ['name' => 'Period 3', 'start' => '09:20', 'end' => '10:00', 'is_break' => false],
            ['name' => 'Short Break', 'start' => '10:00', 'end' => '10:20', 'is_break' => true, 'type' => 'Short Break'],
            ['name' => 'Period 4', 'start' => '10:20', 'end' => '11:00', 'is_break' => false],
            ['name' => 'Period 5', 'start' => '11:00', 'end' => '11:40', 'is_break' => false],
            ['name' => 'Period 6', 'start' => '11:40', 'end' => '12:20', 'is_break' => false],
            ['name' => 'Lunch Break', 'start' => '12:20', 'end' => '13:20', 'is_break' => true, 'type' => 'Lunch'],
            ['name' => 'Period 7', 'start' => '13:20', 'end' => '14:00', 'is_break' => false],
            ['name' => 'Period 8', 'start' => '14:00', 'end' => '14:40', 'is_break' => false],
            ['name' => 'Period 9', 'start' => '14:40', 'end' => '15:20', 'is_break' => false],
            ['name' => 'Games/Clubs', 'start' => '15:20', 'end' => '16:00', 'is_break' => true, 'type' => 'Games'],
        ];

        foreach ($days as $day) {
            foreach ($schedule as $index => $slot) {
                $start = \Carbon\Carbon::createFromFormat('H:i', $slot['start']);
                $end = \Carbon\Carbon::createFromFormat('H:i', $slot['end']);

                TimetablePeriod::create([
                    'academic_year_id' => $academicYear->id,
                    'period_name' => $slot['name'],
                    'day_of_week' => $day,
                    'start_time' => $slot['start'],
                    'end_time' => $slot['end'],
                    'is_break' => $slot['is_break'],
                    'break_type' => $slot['type'] ?? null,
                    'duration_minutes' => $start->diffInMinutes($end),
                    'period_order' => $index + 1,
                    'status' => 'active',
                ]);
            }
        }

        $this->command->info('Periods seeded.');
    }

    protected function seedRooms()
    {
        Schema::disableForeignKeyConstraints();
        TimetableRoom::query()->delete();
        Schema::enableForeignKeyConstraints();

        $rooms = [
            ['name' => 'Form 1A Room', 'capacity' => 40, 'type' => 'classroom'],
            ['name' => 'Form 1B Room', 'capacity' => 40, 'type' => 'classroom'],
            ['name' => 'Form 2A Room', 'capacity' => 40, 'type' => 'classroom'],
            ['name' => 'Form 2B Room', 'capacity' => 40, 'type' => 'classroom'],
            ['name' => 'Form 3A Room', 'capacity' => 40, 'type' => 'classroom'],
            ['name' => 'Form 3B Room', 'capacity' => 40, 'type' => 'classroom'],
            ['name' => 'Form 4A Room', 'capacity' => 40, 'type' => 'classroom'],
            ['name' => 'Form 4B Room', 'capacity' => 40, 'type' => 'classroom'],
            ['name' => 'Science Lab 1', 'capacity' => 30, 'type' => 'laboratory', 'facilities' => ['Water', 'Gas', 'Projector']],
            ['name' => 'Computer Lab', 'capacity' => 30, 'type' => 'laboratory', 'facilities' => ['Computers', 'Internet', 'Projector']],
            ['name' => 'Library', 'capacity' => 60, 'type' => 'hall'],
        ];

        foreach ($rooms as $room) {
            TimetableRoom::create([
                'room_name' => $room['name'],
                'capacity' => $room['capacity'],
                'room_type' => $room['type'],
                'facilities' => $room['facilities'] ?? [],
                'status' => 'available',
            ]);
        }

        $this->command->info('Rooms seeded.');
    }

    protected function seedAllocations($academicYear)
    {
        // Clear existing allocations
        TimetableSubjectAllocation::where('academic_year_id', $academicYear->id)->delete();

        // Get teachers with their qualifications
        $teachers = User::with('teacher.teachingSubjects')->whereHas('roles', function ($q) {
            $q->where('name', 'Teacher');
        })->get();

        if ($teachers->isEmpty()) {
            $this->command->warn('No teachers with "Teacher" role found. Using random users as teachers.');
            // Need more teachers to cover 11 classes * 45 periods = 495 slots
            // With 20 teachers, avg load is ~25 periods (manageable)
            $teachers = User::take(20)->get();
        }

        if ($teachers->isEmpty()) {
            $this->command->warn('No users found at all. Skipping allocation seeding.');
            return;
        }

        $classes = Rank::where('activated', true)->get();
        $subjects = Subject::where('activated', true)->get();

        if ($classes->isEmpty() || $subjects->isEmpty()) {
            $this->command->warn('No classes or subjects found. Skipping allocation seeding.');
            return;
        }

        $workloadService = new WorkloadCalculatorService();
        $allocationsCount = 0;
        $skippedCount = 0;

        // Strategy: For each class, assign all core subjects with qualified teachers
        // Primary Core: Math, Eng, Kis, Sci, SST
        $coreSubjects = ['Mathematics', 'English', 'Kiswahili', 'Science', 'Social Studies'];

        // Split teachers into Lower Primary and Upper Primary groups
        // Assuming ~20 teachers, split 50/50
        $splitIndex = ceil($teachers->count() / 2);
        $lowerPrimaryTeachers = $teachers->take($splitIndex);
        $upperPrimaryTeachers = $teachers->skip($splitIndex);

        $this->command->info("Teacher Distribution: {$lowerPrimaryTeachers->count()} Lower Primary, {$upperPrimaryTeachers->count()} Upper Primary");

        // Track teacher workload to ensure even distribution
        $teacherWorkload = [];
        foreach ($teachers as $teacher) {
            $teacherWorkload[$teacher->id] = 0;
        }

        $this->command->info("Allocating subjects to {$classes->count()} classes with {$teachers->count()} teachers...");

        foreach ($classes as $class) {
            // Skip Secondary classes if they exist
            if (str_contains($class->name, 'Form')) {
                continue;
            }

            // Determine Class Level
            // Lower: PP1, PP2, Grade 1, Grade 2, Grade 3
            // Upper: Grade 4, Grade 5, Grade 6
            $isLowerPrimary = false;
            if (
                str_contains($class->name, 'PP') ||
                str_contains($class->name, 'Grade 1') ||
                str_contains($class->name, 'Grade 2') ||
                str_contains($class->name, 'Grade 3')
            ) {
                $isLowerPrimary = true;
            }

            $eligibleTeachers = $isLowerPrimary ? $lowerPrimaryTeachers : $upperPrimaryTeachers;
            $divisionName = $isLowerPrimary ? "Lower Primary" : "Upper Primary";

            $this->command->info("Processing class: {$class->name} ({$divisionName})");

            foreach ($subjects as $subject) {
                // Determine hours based on subject importance to fill 45 periods
                // Core: 6-7 hours, Others: 4-5 hours
                $hours = in_array($subject->name, $coreSubjects) ? 7 : 4;

                // Find qualified teachers for this subject WITHIN the eligible group
                $qualifiedTeachers = $eligibleTeachers->filter(function ($user) use ($subject) {
                    return $user->teacher && $user->teacher->teachingSubjects->contains('id', $subject->id);
                })->sortBy(function ($teacher) use ($teacherWorkload) {
                    return $teacherWorkload[$teacher->id] ?? 0;
                });

                // If no qualified teachers in the eligible group, try ANY teacher in the eligible group
                if ($qualifiedTeachers->isEmpty()) {
                    $qualifiedTeachers = $eligibleTeachers->sortBy(function ($teacher) use ($teacherWorkload) {
                        return $teacherWorkload[$teacher->id] ?? 0;
                    });
                }

                $assigned = false;

                // Try to assign to a teacher who isn't overloaded
                foreach ($qualifiedTeachers as $teacher) {
                    // Check if this teacher can handle this allocation
                    // For test data, we relax the overload check slightly to ensure coverage
                    $currentLoad = $teacherWorkload[$teacher->id] ?? 0;

                    // Allow up to 35 hours per teacher (approx 7 periods/day)
                    if ($currentLoad + $hours <= 35) {
                        // Create allocation
                        TimetableSubjectAllocation::create([
                            'academic_year_id' => $academicYear->id,
                            'teacher_id' => $teacher->id,
                            'subject_id' => $subject->id,
                            'class_id' => $class->id,
                            'hours_per_week' => $hours,
                            'priority' => in_array($subject->subject_name, $coreSubjects) ? 'high' : 'medium',
                            'status' => 'active',
                            'created_by' => 1, // System/Admin
                        ]);

                        // Update workload tracking
                        $teacherWorkload[$teacher->id] = $currentLoad + $hours;

                        // Recalculate actual workload
                        $workloadService->recalculateTeacherWorkload($teacher->id, $academicYear->id);

                        $allocationsCount++;
                        $assigned = true;
                        break; // Move to next subject
                    }
                }

                // Force assignment if still not assigned (to ensure class coverage)
                if (!$assigned) {
                    // Pick the least loaded teacher from the ELIGIBLE group
                    $teacher = $eligibleTeachers->sortBy(function ($t) use ($teacherWorkload) {
                        return $teacherWorkload[$t->id] ?? 0;
                    })->first();

                    if ($teacher) {
                        TimetableSubjectAllocation::create([
                            'academic_year_id' => $academicYear->id,
                            'teacher_id' => $teacher->id,
                            'subject_id' => $subject->id,
                            'class_id' => $class->id,
                            'hours_per_week' => $hours,
                            'priority' => in_array($subject->subject_name, $coreSubjects) ? 'high' : 'medium',
                            'status' => 'active',
                            'created_by' => 1,
                        ]);
                        $teacherWorkload[$teacher->id] = ($teacherWorkload[$teacher->id] ?? 0) + $hours;
                        $workloadService->recalculateTeacherWorkload($teacher->id, $academicYear->id);
                        $allocationsCount++;
                        $assigned = true;
                        $this->command->info("  Forced assignment: {$subject->subject_name} to {$teacher->first_name} (Overloaded)");
                    }
                }

                if (!$assigned) {
                    $skippedCount++;
                    $this->command->warn("  Skipped: {$subject->subject_name} for {$class->name} (all teachers overloaded)");
                }
            }
        }

        $this->command->info("Allocations seeded: {$allocationsCount} created, {$skippedCount} skipped due to workload limits.");

        // Show teacher distribution
        $this->command->info("\nTeacher Workload Distribution:");
        foreach ($teachers->take(10) as $teacher) {
            $count = TimetableSubjectAllocation::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $academicYear->id)
                ->count();
            $totalHours = TimetableSubjectAllocation::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $academicYear->id)
                ->sum('hours_per_week');
            $this->command->info("  {$teacher->name}: {$count} allocations, {$totalHours} hours/week");
        }
    }

    protected function seedConstraints($academicYear)
    {
        TimetableConstraint::where('academic_year_id', $academicYear->id)->delete();

        $teachers = \App\Models\Teacher::all();

        $rooms = TimetableRoom::where('status', 'available')->get();
        $constraintsCount = 0;

        $this->command->info('Seeding constraints...');

        // 1. CONSECUTIVE PERIODS CONSTRAINT (All Teachers)
        // "Teacher two lesson rest one" -> Max 2 consecutive
        foreach ($teachers as $teacher) {
            TimetableConstraint::create([
                'academic_year_id' => $academicYear->id,
                'constraint_type' => 'consecutive_periods',
                'teacher_id' => $teacher->id,
                'constraint_value' => '2',
                'notes' => 'Max 2 consecutive lessons to prevent teacher fatigue',
            ]);
            $constraintsCount++;
        }

        // 2. TEACHER AVAILABILITY CONSTRAINTS (Varied scenarios)
        $teachersList = $teachers->values();

        // Scenario A: Some teachers unavailable Friday afternoons (Sports/Clubs)
        $sportsTeachers = $teachersList->take(min(3, $teachersList->count()));
        foreach ($sportsTeachers as $teacher) {
            TimetableConstraint::create([
                'academic_year_id' => $academicYear->id,
                'constraint_type' => 'teacher_availability',
                'teacher_id' => $teacher->id,
                'day_of_week' => 'Friday',
                'period_number' => 8, // Late afternoon
                'constraint_value' => 'unavailable',
                'notes' => 'Sports coaching / Club activities',
            ]);
            $constraintsCount++;
        }

        // Scenario B: Some teachers unavailable Monday mornings (Meetings)
        if ($teachersList->count() > 3) {
            $meetingTeachers = $teachersList->slice(3, min(2, $teachersList->count() - 3));
            foreach ($meetingTeachers as $teacher) {
                TimetableConstraint::create([
                    'academic_year_id' => $academicYear->id,
                    'constraint_type' => 'teacher_availability',
                    'teacher_id' => $teacher->id,
                    'day_of_week' => 'Monday',
                    'period_number' => 1, // First period
                    'constraint_value' => 'unavailable',
                    'notes' => 'Department meetings',
                ]);
                $constraintsCount++;
            }
        }

        // Scenario C: Some teachers prefer certain days (Soft constraint)
        if ($teachersList->count() > 5) {
            $preferredTeachers = $teachersList->slice(5, min(2, $teachersList->count() - 5));
            foreach ($preferredTeachers as $teacher) {
                TimetableConstraint::create([
                    'academic_year_id' => $academicYear->id,
                    'constraint_type' => 'teacher_availability',
                    'teacher_id' => $teacher->id,
                    'day_of_week' => 'Wednesday',
                    'period_number' => null, // All periods
                    'constraint_value' => 'preferred',
                    'notes' => 'Prefers teaching on Wednesdays',
                ]);
                $constraintsCount++;
            }
        }

        // 3. ROOM AVAILABILITY CONSTRAINTS
        if ($rooms->isNotEmpty()) {
            // Scenario A: Science lab unavailable during lunch (maintenance)
            $scienceLab = $rooms->firstWhere('room_type', 'laboratory');
            if ($scienceLab) {
                foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
                    TimetableConstraint::create([
                        'academic_year_id' => $academicYear->id,
                        'constraint_type' => 'room_availability',
                        'room_id' => $scienceLab->id,
                        'day_of_week' => $day,
                        'period_number' => 7, // Lunch period
                        'constraint_value' => 'unavailable',
                        'notes' => 'Lab maintenance during lunch',
                    ]);
                    $constraintsCount++;
                }
            }

            // Scenario B: Computer lab unavailable Thursday afternoons (IT maintenance)
            $computerLab = $rooms->where('room_type', 'laboratory')->skip(1)->first();
            if ($computerLab) {
                TimetableConstraint::create([
                    'academic_year_id' => $academicYear->id,
                    'constraint_type' => 'room_availability',
                    'room_id' => $computerLab->id,
                    'day_of_week' => 'Thursday',
                    'period_number' => 8,
                    'constraint_value' => 'unavailable',
                    'notes' => 'IT system maintenance',
                ]);
                $constraintsCount++;
            }
        }

        $this->command->info("Constraints seeded: {$constraintsCount} constraints created.");
        $this->command->info("  - Consecutive periods: {$teachers->count()} (all teachers)");
        $this->command->info("  - Teacher availability: " . ($constraintsCount - $teachers->count() - ($rooms->isNotEmpty() ? 6 : 0)));
        $this->command->info("  - Room availability: " . ($rooms->isNotEmpty() ? 6 : 0));
    }
}
