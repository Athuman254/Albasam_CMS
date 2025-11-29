<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Models\Settings\AcademicYear;
use App\Models\Rank;
use App\Models\Timetable\TimetableAllocation;
use App\Models\Timetable\TimetablePeriod;
use App\Models\Timetable\TimetableVersion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TimetableViewController extends Controller
{
    /**
     * View class timetable.
     */
    public function classTimetable(Request $request, ?int $classId = null)
    {
        $academicYearId = $request->input('academic_year_id', AcademicYear::where('is_active', true)->first()?->id);

        // Get active version
        $version = TimetableVersion::where('academic_year_id', $academicYearId)
            ->where('is_active', true)
            ->first();

        $classes = Rank::with('stream')->where('activated', 1)->orderBy('name')->get();

        if (!$classId && $classes->isNotEmpty()) {
            $classId = $classes->first()->id;
        }

        $allocations = [];
        if ($version && $classId) {
            $allocations = TimetableAllocation::where('version_id', $version->id)
                ->where('class_id', $classId)
                ->with(['subject', 'teacher', 'room'])
                ->get()
                ->groupBy('day_of_week')
                ->map(function ($dayAllocations) {
                    return $dayAllocations->keyBy('period_id');
                });
        }

        $periods = TimetablePeriod::where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->orderBy('period_order')
            ->get();

        return Inertia::render('Timetable/View/ClassTimetable', [
            'classes' => $classes,
            'currentClassId' => (int) $classId,
            'periods' => $periods,
            'allocations' => $allocations,
            'daysOfWeek' => config('timetable.days_of_week'),
            'academicYears' => AcademicYear::orderBy('start_date', 'desc')->get(),
            'currentAcademicYearId' => $academicYearId,
            'hasActiveVersion' => (bool) $version,
        ]);
    }

    /**
     * View teacher timetable.
     */
    public function teacherTimetable(Request $request, ?int $teacherId = null)
    {
        $academicYearId = $request->input('academic_year_id', AcademicYear::where('is_active', true)->first()?->id);

        $version = TimetableVersion::where('academic_year_id', $academicYearId)
            ->where('is_active', true)
            ->first();

        $teachers = User::whereHas('roles', function ($query) {
            $query->where('name', 'Teacher');
        })->orderBy('name')->get();

        if (!$teacherId && $teachers->isNotEmpty()) {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            $teacherId = $user && $user->hasRole('Teacher') ? $user->id : $teachers->first()->id;
        }

        $allocations = [];
        if ($version && $teacherId) {
            $allocations = TimetableAllocation::where('version_id', $version->id)
                ->where('teacher_id', $teacherId)
                ->with(['subject', 'class', 'room'])
                ->get()
                ->groupBy('day_of_week')
                ->map(function ($dayAllocations) {
                    return $dayAllocations->keyBy('period_id');
                });
        }

        $periods = TimetablePeriod::where('academic_year_id', $academicYearId)
            ->where('status', 'active')
            ->orderBy('period_order')
            ->get();

        return Inertia::render('Timetable/View/TeacherTimetable', [
            'teachers' => $teachers,
            'currentTeacherId' => (int) $teacherId,
            'periods' => $periods,
            'allocations' => $allocations,
            'daysOfWeek' => config('timetable.days_of_week'),
            'academicYears' => AcademicYear::orderBy('start_date', 'desc')->get(),
            'currentAcademicYearId' => $academicYearId,
            'hasActiveVersion' => (bool) $version,
        ]);
    }
}
