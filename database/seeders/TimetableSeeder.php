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

        $teachers = User::whereHas('roles', function ($q) {
            $q->where('name', 'Teacher');
        })->get();

        // Fallback: If no teachers found, just grab some users to act as teachers for testing
        if ($teachers->isEmpty()) {
            $this->command->warn('No teachers with "Teacher" role found. Using random users as teachers.');
            $teachers = User::take(10)->get();
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

        // Strategy: Assign core subjects to each class
        $coreSubjects = ['Mathematics', 'English', 'Kiswahili', 'Chemistry', 'Biology', 'Physics'];

        foreach ($classes as $class) {
            foreach ($subjects as $subject) {
                // Determine hours based on subject importance (simplified logic)
                $hours = in_array($subject->subject_name, $coreSubjects) ? 5 : 3;

                // Find a random teacher for this subject
                // In a real scenario, teachers have specializations. Here we pick randomly to ensure data exists.
                // Ideally, we'd check if the teacher teaches this subject, but the User model might not have that link yet.
                // We'll just pick a random teacher to ensure the system works.
                $teacher = $teachers->random();

                // Check overload (simplified check, just don't assign if already overloaded)
                $overload = $workloadService->checkTeacherOverload(
                    $teacher->id,
                    $academicYear->id,
                    1,
                    1,
                    $hours
                );

                if (!$overload['is_overloaded']) {
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

                    $workloadService->recalculateTeacherWorkload($teacher->id, $academicYear->id);
                    $allocationsCount++;
                }
            }
        }

        $this->command->info("Allocations seeded: {$allocationsCount}");
    }

    protected function seedConstraints($academicYear)
    {
        TimetableConstraint::where('academic_year_id', $academicYear->id)->delete();

        $teachers = User::whereHas('roles', function ($q) {
            $q->where('name', 'Teacher');
        })->take(3)->get(); // Just take a few for demo

        if ($teachers->isEmpty()) {
            $teachers = User::take(3)->get();
        }

        foreach ($teachers as $teacher) {
            // Make them unavailable on Friday afternoons
            TimetableConstraint::create([
                'academic_year_id' => $academicYear->id,
                'constraint_type' => 'teacher_availability',
                'teacher_id' => $teacher->id,
                'day_of_week' => 'Friday',
                'period_number' => 8, // Late afternoon
                'constraint_value' => 'unavailable',
                'notes' => 'Sports coaching',
            ]);
        }

        $this->command->info('Constraints seeded.');
    }
}
