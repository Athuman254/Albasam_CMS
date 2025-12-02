<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StudentAttendanceController extends Controller
{
    /**
     * Display a listing of the student's attendance.
     */
    public function index()
    {
        $student = Auth::guard('student')->user();

        $attendance = Attendance::where('student_id', $student->id)
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->id,
                    'date' => $record->date, // Assuming date cast in model or simple string
                    'status' => $record->status,
                    'remarks' => $record->remarks,
                    'formatted_date' => \Carbon\Carbon::parse($record->date)->format('M j, Y'),
                    'day' => \Carbon\Carbon::parse($record->date)->format('l'),
                ];
            });

        // Calculate statistics
        $totalDays = $attendance->count();
        $presentDays = $attendance->where('status', 'present')->count();
        $absentDays = $attendance->where('status', 'absent')->count();
        $lateDays = $attendance->where('status', 'late')->count();

        $attendancePercentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 100;

        return Inertia::render('Student/Attendance/Index', [
            'attendance' => $attendance,
            'stats' => [
                'total_days' => $totalDays,
                'present' => $presentDays,
                'absent' => $absentDays,
                'late' => $lateDays,
                'percentage' => $attendancePercentage
            ]
        ]);
    }
}
