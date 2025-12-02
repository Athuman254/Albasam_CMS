<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('payments:import-bank')
            ->hourly()
            ->between('8:00', '17:00')
            ->weekdays()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/bank-import.log'));

        // Process unmatched payments every 30 minutes
        $schedule->command('payments:process-unmatched')
            ->everyThirtyMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/unmatched-payments.log'));

        // Generate fee reminders for overdue payments daily at 8:00 AM
        $schedule->command('payments:send-overdue-reminders')
            ->dailyAt('8:00')
            ->between('8:00', '17:00')
            ->weekdays()
            ->appendOutputTo(storage_path('logs/fee-reminders.log'));

        // Auto clock-out staff who forgot to clock out at 5:00 PM
        $schedule->command('attendance:auto-clock-out')
            ->dailyAt('17:00')
            ->weekdays()
            ->appendOutputTo(storage_path('logs/attendance-autoclockout.log'));

        // Mark absent staff daily at 6:00 PM
        $schedule->command('attendance:mark-absent')
            ->dailyAt('18:00')
            ->weekdays()
            ->appendOutputTo(storage_path('logs/attendance-absent.log'));

        // Clean up old logs and temporary data weekly
        $schedule->command('app:cleanup-old-data')
            ->weekly()
            ->sundays()
            ->at('23:00')
            ->appendOutputTo(storage_path('logs/cleanup.log'));

        // Backup database daily at midnight
        $schedule->command('backup:run --only-db')
            ->dailyAt('00:00')
            ->appendOutputTo(storage_path('logs/backup.log'));

        // Generate financial reports at the end of each month
        $schedule->command('reports:generate-monthly')
            ->monthlyOn(1, '2:00')
            ->appendOutputTo(storage_path('logs/monthly-reports.log'));

        // Check for system health and send notifications
        $schedule->command('app:system-health-check')
            ->dailyAt('6:00')
            ->appendOutputTo(storage_path('logs/health-check.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Get the timezone that should be used by default for scheduled events.
     *
     * @return \DateTimeZone|string|null
     */
    protected function scheduleTimezone()
    {
        return 'Africa/Nairobi';
    }
}
