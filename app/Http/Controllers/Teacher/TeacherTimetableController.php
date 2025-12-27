<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Settings\AcademicYear;
use App\Models\Timetable\TimetableAllocation;
use App\Models\Timetable\TimetablePeriod;
use App\Models\Timetable\TimetableVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TeacherTimetableController extends Controller
{
    /**
     * Display the teacher's personal timetable.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get all academic years
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        // Get selected academic year or default to active one
        $academicYearId = $request->input('academic_year_id');
        if ($academicYearId) {
            $academicYear = AcademicYear::find($academicYearId);
        } else {
            $academicYear = AcademicYear::where('is_active', true)->first();
        }

        if (!$academicYear) {
            return Inertia::render('Teacher/MyTimetable', [
                'academicYears' => $academicYears,
                'currentAcademicYearId' => null,
                'hasActiveVersion' => false,
                'periods' => [],
                'allocations' => [],
                'daysOfWeek' => config('timetable.days_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
                'teacherName' => $user->name,
            ]);
        }

        // Get the latest active timetable version for this academic year
        $version = TimetableVersion::where('academic_year_id', $academicYear->id)
            ->where('is_active', true)
            ->latest()
            ->first();

        if (!$version) {
            return Inertia::render('Teacher/MyTimetable', [
                'academicYears' => $academicYears,
                'currentAcademicYearId' => $academicYear->id,
                'hasActiveVersion' => false,
                'periods' => [],
                'allocations' => [],
                'daysOfWeek' => config('timetable.days_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
                'teacherName' => $user->name,
            ]);
        }

        // Get all periods for the academic year (only active ones, unique by period_order)
        $periods = TimetablePeriod::where('academic_year_id', $academicYear->id)
            ->where('status', 'active')
            ->orderBy('period_order')
            ->get()
            ->unique('period_order')
            ->values();

        // Get teacher's allocations for this version
        $allocations = [];
        if ($version) {
            $allocations = TimetableAllocation::where('version_id', $version->id)
                ->where('teacher_id', $user->id)
                ->with(['subject', 'class', 'room'])
                ->get()
                ->groupBy('day_of_week')
                ->map(function ($dayAllocations) {
                    return $dayAllocations->keyBy('period_id');
                });
        }

        return Inertia::render('Teacher/MyTimetable', [
            'academicYears' => $academicYears,
            'currentAcademicYearId' => $academicYear->id,
            'hasActiveVersion' => (bool) $version,
            'periods' => $periods,
            'allocations' => $allocations,
            'daysOfWeek' => config('timetable.days_of_week'),
            'teacherName' => $user->name,
            'teacherId' => $user->id,
            'versionId' => $version ? $version->id : null,
        ]);
    }
}
