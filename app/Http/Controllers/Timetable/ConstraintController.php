<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Models\Settings\AcademicYear;
use App\Models\Rank;
use App\Models\Subject;
use App\Models\Timetable\TimetableConstraint;
use App\Models\Timetable\TimetableRoom;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ConstraintController extends Controller
{
    /**
     * Display the constraint management page.
     */
    public function index(Request $request)
    {
        $academicYearId = $request->input('academic_year_id', AcademicYear::where('is_active', true)->first()?->id);

        $constraints = TimetableConstraint::with(['teacher', 'class.stream', 'subject', 'room', 'academicYear'])
            ->where('academic_year_id', $academicYearId)
            ->orderBy('constraint_type')
            ->get()
            ->groupBy('constraint_type');

        $teachers = User::has('teacher')
            ->where('activated', true)
            ->orderBy('name')
            ->get();

        $classes = Rank::with('stream')->where('activated', 1)->orderBy('name')->get();
        $subjects = Subject::where('activated', 1)->orderBy('name')->get();
        $rooms = TimetableRoom::where('status', 'available')->orderBy('room_name')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return Inertia::render('Timetable/Setup/Constraints', [
            'constraints' => $constraints,
            'teachers' => $teachers,
            'classes' => $classes,
            'subjects' => $subjects,
            'rooms' => $rooms,
            'academicYears' => $academicYears,
            'currentAcademicYearId' => $academicYearId,
            'constraintTypes' => config('timetable.constraint_types'),
            'daysOfWeek' => config('timetable.days_of_week'),
        ]);
    }

    /**
     * Store a new constraint.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'constraint_type' => 'required|string',
            'teacher_id' => 'nullable|exists:users,id',
            'class_id' => 'nullable|exists:ranks,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'room_id' => 'nullable|exists:timetable_rooms,id',
            'day_of_week' => 'nullable|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'period_number' => 'nullable|integer',
            'constraint_value' => 'required|in:available,unavailable,preferred,not_preferred,required',
            'notes' => 'nullable|string',
        ]);

        // Validate required fields based on constraint type
        if ($validated['constraint_type'] === 'teacher_availability' && empty($validated['teacher_id'])) {
            return back()->withErrors(['teacher_id' => 'Teacher is required for teacher availability constraint.']);
        }
        if ($validated['constraint_type'] === 'room_availability' && empty($validated['room_id'])) {
            return back()->withErrors(['room_id' => 'Room is required for room availability constraint.']);
        }

        TimetableConstraint::create($validated);

        return back()->with('success', 'Constraint added successfully.');
    }

    /**
     * Update a constraint.
     */
    public function update(Request $request, TimetableConstraint $constraint)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'constraint_type' => 'required|string',
            'teacher_id' => 'nullable|exists:users,id',
            'class_id' => 'nullable|exists:ranks,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'room_id' => 'nullable|exists:timetable_rooms,id',
            'day_of_week' => 'nullable|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'period_number' => 'nullable|integer',
            'constraint_value' => 'required|in:available,unavailable,preferred,not_preferred,required',
            'notes' => 'nullable|string',
        ]);

        // Validate required fields based on constraint type
        if ($validated['constraint_type'] === 'teacher_availability' && empty($validated['teacher_id'])) {
            return back()->withErrors(['teacher_id' => 'Teacher is required for teacher availability constraint.']);
        }
        if ($validated['constraint_type'] === 'room_availability' && empty($validated['room_id'])) {
            return back()->withErrors(['room_id' => 'Room is required for room availability constraint.']);
        }

        $constraint->update($validated);

        return back()->with('success', 'Constraint updated successfully.');
    }

    /**
     * Delete a constraint.
     */
    public function destroy(TimetableConstraint $constraint)
    {
        $constraint->delete();

        return back()->with('success', 'Constraint deleted successfully.');
    }
}
