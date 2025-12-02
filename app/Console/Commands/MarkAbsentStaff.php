<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\StaffAttendance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkAbsentStaff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:mark-absent {--date= : The date to mark absent (Y-m-d format, defaults to today)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark staff who did not clock in as absent';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : Carbon::today();

        $this->info("Marking absent staff for date: " . $date->format('Y-m-d'));

        // Get all active employees
        $employees = Employee::all();
        $markedAbsent = 0;
        $alreadyMarked = 0;

        foreach ($employees as $employee) {
            // Check if attendance record exists for this date
            $attendance = StaffAttendance::where('employee_id', $employee->id)
                ->where('date', $date)
                ->first();

            if (!$attendance) {
                // No attendance record - mark as absent
                StaffAttendance::create([
                    'employee_id' => $employee->id,
                    'date' => $date,
                    'clock_in_time' => null,
                    'status' => 'absent',
                    'is_late' => false,
                    'notes' => 'Auto-marked absent - did not clock in',
                ]);

                $markedAbsent++;
                $this->line("  ✓ Marked {$employee->first_name} {$employee->last_name} as absent");
            } else {
                $alreadyMarked++;
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->line("  Total employees: " . $employees->count());
        $this->line("  Marked absent: {$markedAbsent}");
        $this->line("  Already had attendance: {$alreadyMarked}");

        return Command::SUCCESS;
    }
}
