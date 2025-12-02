<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamMark;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Controllers\Exams\ExamResultController;

class StudentResultController extends Controller
{
    /**
     * Display a listing of the student's exam results.
     */
    public function index()
    {
        $student = Auth::guard('student')->user();

        // Get exams where the student has published marks
        $exams = Exam::whereHas('marks', function ($query) use ($student) {
            $query->where('student_id', $student->id)
                ->where('status', ExamMark::PUBLISHED);
        })
            ->with(['academicYear'])
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function ($exam) use ($student) {
                // Calculate summary for this exam
                $marks = ExamMark::where('exam_id', $exam->id)
                    ->where('student_id', $student->id)
                    ->where('status', ExamMark::PUBLISHED)
                    ->get();

                $totalMarks = $marks->sum('marks_obtained');
                $maxMarks = $marks->sum(function ($mark) {
                    return $mark->examSubject->max_marks ?? 0;
                });

                $percentage = $maxMarks > 0 ? ($totalMarks / $maxMarks) * 100 : 0;

                return [
                    'id' => $exam->id,
                    'name' => $exam->name,
                    'academic_year' => $exam->academicYear->name ?? 'N/A',
                    'term' => $exam->term ?? 'N/A',
                    'date' => $exam->start_date ? $exam->start_date->format('M Y') : 'N/A',
                    'subjects_count' => $marks->count(),
                    'average_grade' => $this->calculateGrade($percentage),
                    'percentage' => round($percentage, 1),
                ];
            });

        return Inertia::render('Student/Results/Index', [
            'exams' => $exams
        ]);
    }

    /**
     * Display the specified exam result.
     */
    public function show($examId)
    {
        $student = Auth::guard('student')->user();
        $exam = Exam::with('academicYear')->findOrFail($examId);

        // Fetch published marks for this student and exam
        $marks = ExamMark::with(['examSubject.subject'])
            ->where('exam_id', $examId)
            ->where('student_id', $student->id)
            ->where('status', ExamMark::PUBLISHED)
            ->get();

        if ($marks->isEmpty()) {
            return redirect()->route('student.results.index')
                ->with('error', 'Results not found or not yet published.');
        }

        // Calculate totals and averages
        $totalMarks = $marks->sum('marks_obtained');
        $maxMarks = $marks->sum(function ($mark) {
            return $mark->examSubject->max_marks ?? 0;
        });
        $percentage = $maxMarks > 0 ? ($totalMarks / $maxMarks) * 100 : 0;

        // Prepare chart data
        $chartData = [
            'labels' => $marks->map(fn($m) => $m->examSubject->subject->code ?? $m->examSubject->subject->name),
            'data' => $marks->pluck('marks_obtained'),
        ];

        return Inertia::render('Student/Results/Show', [
            'exam' => [
                'id' => $exam->id,
                'name' => $exam->name,
                'academic_year' => $exam->academicYear->name ?? 'N/A',
            ],
            'results' => $marks->map(function ($mark) {
                return [
                    'subject' => $mark->examSubject->subject->name,
                    'marks' => $mark->marks_obtained,
                    'max_marks' => $mark->examSubject->max_marks,
                    'grade' => $mark->grade,
                    'remarks' => $mark->remarks,
                ];
            }),
            'summary' => [
                'total_marks' => $totalMarks,
                'max_marks' => $maxMarks,
                'percentage' => round($percentage, 1),
                'grade' => $this->calculateGrade($percentage),
            ],
            'chartData' => $chartData
        ]);
    }

    /**
     * Download the exam result as PDF.
     */
    public function download(Request $request, $examId)
    {
        $student = Auth::guard('student')->user();

        // Reuse the existing ExamResultController logic
        // We need to construct a request that mimics what generateStudentReport expects
        $request->merge([
            'exam_id' => $examId,
            'class_id' => $student->rank_id, // Assuming current class, might need history if we track it
            'only_published' => true,
        ]);

        $controller = app(ExamResultController::class);
        return $controller->generateStudentReport($request, $student->id);
    }

    private function calculateGrade($percentage)
    {
        // Simple grading logic - should ideally come from a service or database
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
