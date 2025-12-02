<?php

namespace App\Console\Commands;

use App\Models\StaffAttendance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoClockOutStaff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-clock-out {--time=17:00 : The time to set as clock out time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically clock out staff who forgot to clock out';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $clockOutTimeStr = $this->option('time');
        $clockOutTime = Carbon::parse($today->format('Y-m-d') . ' ' . $clockOutTimeStr);

        $this->info("Auto-clocking out staff for date: " . $today->format('Y-m-d'));
        $this->info("Setting clock-out time to: " . $clockOutTime->format('h:i A'));

        // Find staff who clocked in today but haven't clocked out
        $attendances = StaffAttendance::where('date', $today)
            ->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time')
            ->where('status', '!=', 'absent')
            ->get();

        $count = 0;

        foreach ($attendances as $attendance) {
            // Calculate total hours
            $clockIn = Carbon::parse($attendance->clock_in_time);
            $totalHours = round($clockOutTime->diffInMinutes($clockIn) / 60, 2);

            // Determine status (if hours < 4, mark as half day, otherwise keep present)
            // Note: If they were already marked 'present' (even if late), we keep it unless hours are too low
            $status = $attendance->status;
            if ($totalHours < 4) {
                $status = 'half_day';
            }

            $attendance->update([
                'clock_out_time' => $clockOutTime,
                'status' => $status,
                'notes' => ($attendance->notes ? $attendance->notes . "\n" : "") . "Auto-clocked out by system",
            ]);

            $this->line("  ✓ Clocked out {$attendance->employee->first_name} {$attendance->employee->last_name} (Hours: {$totalHours})");
            $count++;
        }

        $this->newLine();
        $this->info("Summary:");
        $this->line("  Total auto-clocked out: {$count}");

        return Command::SUCCESS;
    }
}
