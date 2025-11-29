<?php

namespace App\Services\Timetable;

use App\Models\Rank;
use App\Models\Timetable\TimetableSubjectAllocation;
use Illuminate\Support\Facades\Config;

class TeacherDivisionService
{
    /**
     * Get the division for a given class
     * 
     * @param int $classId
     * @return string 'lower_primary', 'upper_primary', 'secondary', or 'unknown'
     */
    public function getClassDivision(int $classId): string
    {
        $class = Rank::find($classId);

        if (!$class) {
            return 'unknown';
        }

        $className = $class->name;

        // Check Lower Primary
        $lowerPrimary = ['PP1', 'PP2', 'Grade 1', 'Grade 2', 'Grade 3'];
        foreach ($lowerPrimary as $grade) {
            if (str_contains($className, $grade)) {
                return 'lower_primary';
            }
        }

        // Check Upper Primary
        $upperPrimary = ['Grade 4', 'Grade 5', 'Grade 6'];
        foreach ($upperPrimary as $grade) {
            if (str_contains($className, $grade)) {
                return 'upper_primary';
            }
        }

        // Check Secondary
        $secondary = ['Form 1', 'Form 2', 'Form 3', 'Form 4'];
        foreach ($secondary as $form) {
            if (str_contains($className, $form)) {
                return 'secondary';
            }
        }

        return 'unknown';
    }

    /**
     * Get the primary division for a teacher based on their current allocations
     * 
     * @param int $teacherId
     * @return string 'lower_primary', 'upper_primary', 'secondary', 'mixed', or 'none'
     */
    public function getTeacherDivision(int $teacherId): string
    {
        $allocations = TimetableSubjectAllocation::where('teacher_id', $teacherId)
            ->where('status', 'active')
            ->with('class')
            ->get();

        if ($allocations->isEmpty()) {
            return 'none'; // No allocations yet, can teach anywhere
        }

        $divisions = [];
        foreach ($allocations as $allocation) {
            $division = $this->getClassDivision($allocation->class_id);
            if ($division !== 'unknown') {
                $divisions[] = $division;
            }
        }

        $uniqueDivisions = array_unique($divisions);

        if (count($uniqueDivisions) === 0) {
            return 'none';
        }

        if (count($uniqueDivisions) === 1) {
            return $uniqueDivisions[0];
        }

        return 'mixed'; // Teacher teaches across multiple divisions
    }

    /**
     * Check if a teacher can teach a specific class based on division rules
     * 
     * @param int $teacherId
     * @param int $classId
     * @return array ['allowed' => bool, 'reason' => string|null]
     */
    public function canTeacherTeachClass(int $teacherId, int $classId): array
    {
        $teacherDivision = $this->getTeacherDivision($teacherId);
        $classDivision = $this->getClassDivision($classId);

        // If teacher has no allocations yet, allow any assignment
        if ($teacherDivision === 'none') {
            return ['allowed' => true, 'reason' => null];
        }

        // If teacher already teaches across divisions, allow (they're already mixed)
        if ($teacherDivision === 'mixed') {
            return ['allowed' => true, 'reason' => null];
        }

        // If class division is unknown, allow (edge case)
        if ($classDivision === 'unknown') {
            return ['allowed' => true, 'reason' => null];
        }

        // Check if divisions match
        if ($teacherDivision === $classDivision) {
            return ['allowed' => true, 'reason' => null];
        }

        // Divisions don't match
        $divisionNames = [
            'lower_primary' => 'Lower Primary (PP1-Grade 3)',
            'upper_primary' => 'Upper Primary (Grade 4-6)',
            'secondary' => 'Secondary (Form 1-4)',
        ];

        $teacherDivisionName = $divisionNames[$teacherDivision] ?? $teacherDivision;
        $classDivisionName = $divisionNames[$classDivision] ?? $classDivision;

        return [
            'allowed' => false,
            'reason' => "This teacher is currently assigned to {$teacherDivisionName} classes. The selected class is {$classDivisionName}. Cross-division assignments are not recommended."
        ];
    }

    /**
     * Get division label for display
     * 
     * @param string $division
     * @return string
     */
    public function getDivisionLabel(string $division): string
    {
        $labels = [
            'lower_primary' => 'Lower Primary',
            'upper_primary' => 'Upper Primary',
            'secondary' => 'Secondary',
            'mixed' => 'Mixed Divisions',
            'none' => 'No Assignments',
            'unknown' => 'Unknown',
        ];

        return $labels[$division] ?? $division;
    }
}
