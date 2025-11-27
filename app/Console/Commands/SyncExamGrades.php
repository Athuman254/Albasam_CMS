<?php

namespace App\Console\Commands;

use App\Models\ExamMark;
use App\Services\GradingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncExamGrades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exams:sync-grades';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate grades for all exam marks using the standard Kenyan grading system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting grade synchronization...');

        $query = ExamMark::query()->whereNotNull('marks_obtained');
        $total = $query->count();
        $bar = $this->output->createProgressBar($total);
        $updated = 0;

        $query->chunk(100, function ($marks) use ($bar, &$updated) {
            foreach ($marks as $mark) {
                // Calculate percentage
                $percentage = $mark->percentage();

                if ($percentage !== null) {
                    $newGrade = GradingService::getGrade($percentage);

                    if ($mark->grade !== $newGrade) {
                        // Update directly to avoid triggering other events if not needed, 
                        // or use save() if we want observers to run. 
                        // Using update() for speed and to strictly update grade.
                        DB::table('exam_marks')
                            ->where('id', $mark->id)
                            ->update(['grade' => $newGrade]);

                        $updated++;
                    }
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Synchronization complete. Updated {$updated} marks.");
    }
}
