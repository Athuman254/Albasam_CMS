<?php

namespace App\Services\Timetable;

use App\Models\Timetable\TimetableAllocation;
use App\Models\Timetable\TimetableVersion;
use App\Models\Timetable\TimetablePeriod;
use App\Models\Rank;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class TimetablePdfService
{
    /**
     * Generate PDF for a specific class timetable
     */
    public function generateClassTimetable(int $versionId, int $classId)
    {
        $version = TimetableVersion::with('academicYear')->findOrFail($versionId);
        $class = Rank::with('stream')->findOrFail($classId);

        // Get unique periods (including breaks) for the structure
        $periods = TimetablePeriod::where('academic_year_id', $version->academic_year_id)
            ->where('status', 'active')
            ->orderBy('period_order')
            ->get()
            ->unique('period_order')
            ->values();

        // Get allocations for this class and version
        $allocations = TimetableAllocation::where('version_id', $versionId)
            ->where('class_id', $classId)
            ->with(['subject', 'teacher', 'period'])
            ->get();

        // Organize into grid: days x period_order
        $days = config('timetable.days_of_week');
        $timetableGrid = $this->buildGridByOrder($allocations, $days, $periods);

        $pdf = Pdf::loadView('timetable.pdf.class_timetable', [
            'version' => $version,
            'class' => $class,
            'days' => $days,
            'periods' => $periods,
            'timetableGrid' => $timetableGrid,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("timetable_{$class->name}_{$version->version_name}.pdf");
    }

    /**
     * Generate PDF for a specific teacher timetable
     */
    public function generateTeacherTimetable(int $versionId, int $teacherId)
    {
        $version = TimetableVersion::with('academicYear')->findOrFail($versionId);
        $teacher = User::findOrFail($teacherId);

        // Get unique periods (including breaks) for the structure
        $periods = TimetablePeriod::where('academic_year_id', $version->academic_year_id)
            ->where('status', 'active')
            ->orderBy('period_order')
            ->get()
            ->unique('period_order')
            ->values();

        // Get allocations for this teacher and version
        $allocations = TimetableAllocation::where('version_id', $versionId)
            ->where('teacher_id', $teacherId)
            ->with(['subject', 'class', 'period'])
            ->get();

        // Organize into grid: days x period_order
        $days = config('timetable.days_of_week');
        $timetableGrid = $this->buildGridByOrder($allocations, $days, $periods);

        $pdf = Pdf::loadView('timetable.pdf.teacher_timetable', [
            'version' => $version,
            'teacher' => $teacher,
            'days' => $days,
            'periods' => $periods,
            'timetableGrid' => $timetableGrid,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("timetable_teacher_{$teacher->name}_{$version->version_name}.pdf");
    }

    /**
     * Generate PDF for all classes
     */
    public function generateAllClassesTimetable(int $versionId)
    {
        $version = TimetableVersion::with('academicYear')->findOrFail($versionId);

        // Get all classes that have allocations in this version
        $classIds = TimetableAllocation::where('version_id', $versionId)
            ->distinct()
            ->pluck('class_id');

        $classes = Rank::with('stream')->whereIn('id', $classIds)->orderBy('name')->get();

        // Get unique periods (including breaks)
        $periods = TimetablePeriod::where('academic_year_id', $version->academic_year_id)
            ->where('status', 'active')
            ->orderBy('period_order')
            ->get()
            ->unique('period_order')
            ->values();

        $days = config('timetable.days_of_week');

        // Build grid for each class
        $classTimetables = [];
        foreach ($classes as $class) {
            $allocations = TimetableAllocation::where('version_id', $versionId)
                ->where('class_id', $class->id)
                ->with(['subject', 'teacher', 'period'])
                ->get();

            $classTimetables[$class->id] = [
                'class' => $class,
                'grid' => $this->buildGridByOrder($allocations, $days, $periods),
            ];
        }

        $pdf = Pdf::loadView('timetable.pdf.all_classes_timetable', [
            'version' => $version,
            'days' => $days,
            'periods' => $periods,
            'classTimetables' => $classTimetables,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("timetable_all_classes_{$version->version_name}.pdf");
    }

    /**
     * Build a grid structure from allocations using period_order
     */
    private function buildGridByOrder($allocations, $days, $periods)
    {
        $grid = [];

        // Initialize empty grid
        foreach ($days as $dayIndex => $day) {
            foreach ($periods as $period) {
                $grid[$day][$period->period_order] = null;
            }
        }

        // Fill grid with allocations
        foreach ($allocations as $allocation) {
            $day = $allocation->day_of_week;
            // Use period_number from allocation if available, or fetch from relation
            $periodOrder = $allocation->period_number ?? $allocation->period->period_order;

            if (isset($grid[$day]) && array_key_exists($periodOrder, $grid[$day])) {
                $grid[$day][$periodOrder] = $allocation;
            }
        }

        return $grid;
    }
}
