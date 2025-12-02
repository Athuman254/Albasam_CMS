<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Fee;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixFeeCarryOverStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fees:fix-carry-over';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix fee statuses by marking fees that have been carried over as "carried_over"';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting fee carry-over fix...');

        $carryOverFees = Fee::where('fee_type', 'carry_over')
            ->orWhere('is_carry_over', true)
            ->orderBy('academic_year', 'asc')
            ->orderBy('term', 'asc')
            ->get();

        $this->info("Found {$carryOverFees->count()} carry-over fee records.");

        $bar = $this->output->createProgressBar($carryOverFees->count());
        $updatedCount = 0;

        foreach ($carryOverFees as $carryOverFee) {
            $studentId = $carryOverFee->student_id;
            $year = $carryOverFee->academic_year;
            $term = $carryOverFee->term;

            // Find all previous fees for this student that should have been carried over
            $previousFees = Fee::where('student_id', $studentId)
                ->where('id', '!=', $carryOverFee->id) // Exclude self
                ->where(function ($query) use ($year, $term) {
                    $query->where('academic_year', $year)
                        ->where('term', '<', $term)
                        ->orWhere('academic_year', '<', $year);
                })
                ->where('balance', '>', 0) // Only fees with balance were carried over
                ->where('status', '!=', 'carried_over')
                ->get();

            if ($previousFees->isNotEmpty()) {
                foreach ($previousFees as $prevFee) {
                    $prevFee->update(['status' => 'carried_over']);
                    $updatedCount++;
                    Log::info("Updated fee {$prevFee->id} to carried_over status. (Carried over by {$carryOverFee->id})");
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Completed! Updated {$updatedCount} fee records to 'carried_over' status.");
    }
}
