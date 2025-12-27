<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\LessonMaterial;
use App\Models\OnlineClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StudentLmsController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();

        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Student record not found.');
        }

        $upcomingClasses = OnlineClass::where('rank_id', $student->rank_id)
            ->where('status', '!=', 'completed')
            ->where('scheduled_at', '>', now()->subHours(4))
            ->with(['subject', 'teacher:id,name'])
            ->orderBy('scheduled_at', 'asc')
            ->limit(5)
            ->get();

        $pendingAssignments = Assignment::where('rank_id', $student->rank_id)
            ->published()
            ->upcoming()
            ->with(['subject'])
            ->whereDoesntHave('submissions', function ($q) use ($student) {
                $q->where('student_id', $student->id);
            })
            ->limit(5)
            ->get();

        $totalMaterials = LessonMaterial::where('rank_id', $student->rank_id)
            ->published()
            ->count();

        $totalAssignments = Assignment::where('rank_id', $student->rank_id)
            ->published()
            ->count();

        $totalClasses = OnlineClass::where('rank_id', $student->rank_id)
            ->upcoming()
            ->count();

        return Inertia::render('Student/Lms/Dashboard', [
            'upcomingClasses' => $upcomingClasses,
            'pendingAssignments' => $pendingAssignments,
            'studentClass' => $student->rank?->name,
            'counts' => [
                'materials' => $totalMaterials,
                'assignments' => $totalAssignments,
                'classes' => $totalClasses,
                'pending_assignments' => $pendingAssignments->count(),
            ]
        ]);
    }

    public function materials()
    {
        $student = Auth::guard('student')->user();

        $materials = LessonMaterial::where('rank_id', $student->rank_id)
            ->published()
            ->with(['subject', 'teacher:id,name'])
            ->latest()
            ->paginate(12);

        return Inertia::render('Student/Lms/Materials', [
            'materials' => $materials,
        ]);
    }

    public function assignments()
    {
        $student = Auth::guard('student')->user();

        $assignments = Assignment::where('rank_id', $student->rank_id)
            ->published()
            ->with(['subject', 'teacher:id,name'])
            ->with(['submissions' => function ($q) use ($student) {
                $q->where('student_id', $student->id);
            }])
            ->latest()
            ->paginate(10);

        return Inertia::render('Student/Lms/Assignments', [
            'assignments' => $assignments,
        ]);
    }

    public function submitAssignment(Request $request, Assignment $assignment)
    {
        $student = Auth::guard('student')->user();

        $request->validate([
            'file' => 'required|file|max:10240', // 10MB
            'notes' => 'nullable|string',
        ]);

        if ($assignment->rank_id !== $student->rank_id) {
            return back()->with('error', 'You are not authorized to submit this assignment.');
        }

        if ($assignment->isOverdue() && !$assignment->allow_late_submission) {
            return back()->with('error', 'Late submissions are not allowed for this assignment.');
        }

        $filePath = $request->file('file')->store('lms/submissions', 'public');

        AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => $student->id],
            [
                'submission_file' => $filePath,
                'submission_text' => $request->notes,
                'submitted_at' => now(),
                'status' => 'submitted',
            ]
        );

        return back()->with('success', 'Assignment submitted successfully.');
    }

    public function classes()
    {
        $student = Auth::guard('student')->user();

        $classes = OnlineClass::where('rank_id', $student->rank_id)
            ->with(['subject', 'teacher:id,name'])
            ->latest('scheduled_at')
            ->paginate(10);

        return Inertia::render('Student/Lms/Classes', [
            'onlineClasses' => $classes,
        ]);
    }
}
