<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Exam;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();
        $student->load(['rank', 'fees', 'feePayments']);

        // Calculate attendance percentage
        $totalAttendance = Attendance::where('student_id', $student->id)->count();
        $presentDays = Attendance::where('student_id', $student->id)
            ->where('status', 'present')
            ->count();
        $attendancePercentage = $totalAttendance > 0 ? round(($presentDays / $totalAttendance) * 100) : 0;

        // Get latest exam grade
        $latestExam = Exam::whereHas('marks', function ($query) use ($student) {
            $query->where('student_id', $student->id)
                ->where('status', 'published');
        })
            ->orderBy('start_date', 'desc')
            ->first();

        $currentGrade = 'N/A';
        if ($latestExam) {
            $marks = $latestExam->marks()
                ->where('student_id', $student->id)
                ->where('status', 'published')
                ->get();

            $totalMarks = $marks->sum('marks_obtained');
            $maxMarks = $marks->sum(function ($mark) {
                return $mark->examSubject->max_marks ?? 0;
            });

            if ($maxMarks > 0) {
                $percentage = ($totalMarks / $maxMarks) * 100;
                $currentGrade = $this->calculateGrade($percentage);
            }
        }

        // Get fee balance
        $feeBalance = $student->balance ?? 0;

        // Get upcoming exams count
        $upcomingExams = Exam::where('status', 'active')
            ->where('start_date', '>=', now())
            ->count();

        $stats = [
            'attendance_percentage' => $attendancePercentage,
            'current_grade' => $currentGrade,
            'fee_balance' => $feeBalance,
            'upcoming_exams' => $upcomingExams,
        ];

        // Get recent notices
        $notices = \App\Models\Notice::active()
            ->published()
            ->forStudents()
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($notice) {
                return [
                    'id' => $notice->id,
                    'title' => $notice->title,
                    'published_at' => $notice->published_at ? $notice->published_at->diffForHumans() : $notice->created_at->diffForHumans(),
                    'type' => $notice->type,
                ];
            });

        return Inertia::render('Student/Dashboard', [
            'student' => $student,
            'stats' => $stats,
            'notices' => $notices,
        ]);
    }

    private function calculateGrade($percentage)
    {
        if ($percentage >= 80) return 'A';
        if ($percentage >= 75) return 'A-';
        if ($percentage >= 70) return 'B+';
        if ($percentage >= 65) return 'B';
        if ($percentage >= 60) return 'B-';
        if ($percentage >= 55) return 'C+';
        if ($percentage >= 50) return 'C';
        if ($percentage >= 45) return 'C-';
        if ($percentage >= 40) return 'D+';
        if ($percentage >= 35) return 'D';
        if ($percentage >= 30) return 'D-';
        return 'E';
    }
}
