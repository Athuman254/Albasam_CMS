<?php

namespace App\Console\Commands;

use App\Models\Settings\AcademicYear;
use Illuminate\Console\Command;

class GenerateAcademicYears extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'academic-years:generate {count=5 : Number of years to generate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-generate academic years starting from current year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->argument('count');

        $this->info("Generating {$count} academic years...");

        $beforeCount = AcademicYear::count();

        AcademicYear::autoGenerate($count);

        $afterCount = AcademicYear::count();
        $generated = $afterCount - $beforeCount;

        if ($generated > 0) {
            $this->info("✓ Successfully generated {$generated} new academic year(s)");
        } else {
            $this->info("✓ All academic years already exist");
        }

        $this->newLine();
        $this->table(
            ['ID', 'Name', 'Display Name', 'Start Date', 'End Date', 'Active'],
            AcademicYear::orderBy('id')->get()->map(function ($year) {
                return [
                    $year->id,
                    $year->name,
                    $year->display_name,
                    $year->start_date->format('Y-m-d'),
                    $year->end_date->format('Y-m-d'),
                    $year->is_active ? 'Yes' : 'No',
                ];
            })
        );

        return Command::SUCCESS;
    }
}
