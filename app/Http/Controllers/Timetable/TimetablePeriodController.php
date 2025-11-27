<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Models\Settings\AcademicYear;
use App\Models\Timetable\TimetablePeriod;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TimetablePeriodController extends Controller
{
    /**
     * Display the period management page.
     */
    public function index(Request $request)
    {
        $academicYearId = $request->input('academic_year_id', AcademicYear::where('is_active', true)->first()?->id);

        $periods = TimetablePeriod::with('academicYear')
            ->where('academic_year_id', $academicYearId)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return Inertia::render('Timetable/Setup/Periods', [
            'periods' => $periods,
            'academicYears' => $academicYears,
            'currentAcademicYearId' => $academicYearId,
            'daysOfWeek' => config('timetable.days_of_week'),
            'periodDurations' => config('timetable.periods'),
        ]);
    }

    /**
     * Store a new period.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'period_name' => 'required|string|max:255',
            'days_of_week' => 'required|array|min:1',
            'days_of_week.*' => 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'is_break' => 'boolean',
            'break_type' => 'nullable|string|max:255',
            'duration_minutes' => 'nullable|integer|min:1',
        ]);

        // Calculate duration and end time
        $startTime = \Carbon\Carbon::createFromFormat('H:i', $validated['start_time']);
        $durationMinutes = $validated['duration_minutes'] ?? config('timetable.periods.default_lesson_duration');

        if ($validated['is_break'] ?? false) {
            $breakType = $validated['break_type'] ?? 'Short Break';
            $durationMinutes = match ($breakType) {
                'Lunch' => config('timetable.periods.lunch_duration'),
                'Assembly' => config('timetable.periods.assembly_duration'),
                default => config('timetable.periods.short_break_duration'),
            };
        }

        $endTime = $validated['end_time']
            ? \Carbon\Carbon::createFromFormat('H:i', $validated['end_time'])
            : $startTime->copy()->addMinutes($durationMinutes);

        // Create period for each selected day
        $createdPeriods = [];
        foreach ($validated['days_of_week'] as $day) {
            $period = TimetablePeriod::create([
                'academic_year_id' => $validated['academic_year_id'],
                'period_name' => $validated['period_name'],
                'day_of_week' => $day,
                'start_time' => $startTime->format('H:i:s'),
                'end_time' => $endTime->format('H:i:s'),
                'is_break' => $validated['is_break'] ?? false,
                'break_type' => $validated['break_type'] ?? null,
                'duration_minutes' => $durationMinutes,
                'status' => 'active',
            ]);
            $createdPeriods[] = $period;
        }

        return back()->with('success', 'Period(s) created successfully for ' . count($createdPeriods) . ' day(s).');
    }

    /**
     * Update a period.
     */
    public function update(Request $request, TimetablePeriod $period)
    {
        $validated = $request->validate([
            'period_name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'is_break' => 'boolean',
            'break_type' => 'nullable|string|max:255',
            'status' => 'in:active,inactive',
        ]);

        $startTime = \Carbon\Carbon::createFromFormat('H:i', $validated['start_time']);
        $endTime = \Carbon\Carbon::createFromFormat('H:i', $validated['end_time']);
        $durationMinutes = $startTime->diffInMinutes($endTime);

        $period->update([
            ...$validated,
            'start_time' => $startTime->format('H:i:s'),
            'end_time' => $endTime->format('H:i:s'),
            'duration_minutes' => $durationMinutes,
        ]);

        return back()->with('success', 'Period updated successfully.');
    }

    /**
     * Delete a period.
     */
    public function destroy(TimetablePeriod $period)
    {
        // Check if period has allocations
        if ($period->allocations()->exists()) {
            return back()->with('error', 'Cannot delete period that has timetable allocations.');
        }

        $period->delete();

        return back()->with('success', 'Period deleted successfully.');
    }

    /**
     * Bulk delete periods.
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'period_ids' => 'required|array|min:1',
            'period_ids.*' => 'exists:timetable_periods,id',
        ]);

        $periods = TimetablePeriod::whereIn('id', $validated['period_ids'])->get();

        // Check if any period has allocations
        foreach ($periods as $period) {
            if ($period->allocations()->exists()) {
                return back()->with('error', 'Cannot delete periods that have timetable allocations.');
            }
        }

        TimetablePeriod::whereIn('id', $validated['period_ids'])->delete();

        return back()->with('success', count($validated['period_ids']) . ' period(s) deleted successfully.');
    }
}
