<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\LessonMaterial;
use App\Models\Assignment;
use App\Models\OnlineClass;
use App\Models\AssignmentSubmission;
use App\Models\Rank;
use App\Models\Subject;
use Illuminate\Support\Facades\Storage;
use Spatie\QueryBuilder\QueryBuilder;

class LmsController extends Controller
{
    /**
     * Display a listing of lesson materials.
     */
    public function materials()
    {
        $materials = QueryBuilder::for(LessonMaterial::class)
            // teacher relation uses users table which has 'name' column
            ->with(['subject', 'rank', 'teacher:id,name'])
            ->allowedFilters(['subject_id', 'rank_id'])
            ->defaultSort('-created_at')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Admin/Lms/Materials', [
            'materials' => $materials,
            'subjects' => Subject::active()->get(),
            'classes' => Rank::all(),
            'filters' => request()->all('subject_id', 'rank_id'),
        ]);
    }

    /**
     * Store a newly created lesson material.
     */
    public function storeMaterial(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'rank_id' => 'required|exists:ranks,id',
            'description' => 'nullable|string',
            'material_type' => 'required|in:document,video,link,audio',
            'file' => 'required_unless:material_type,link|file|max:20480', // 20MB
            'link' => 'required_if:material_type,link|nullable|url',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('lms/materials', 'public');
        } elseif ($validated['material_type'] === 'link') {
            $filePath = $validated['link'];
        }

        LessonMaterial::create([
            'title' => $validated['title'],
            'subject_id' => $validated['subject_id'],
            'rank_id' => $validated['rank_id'],
            'teacher_id' => auth()->id(),
            'description' => $validated['description'],
            'material_type' => $validated['material_type'],
            'file_path' => $filePath,
            'file_type' => $request->hasFile('file') ? $request->file('file')->getClientOriginalExtension() : null,
            'is_published' => true,
        ]);

        return back()->with('success', 'Material uploaded successfully.');
    }

    /**
     * Remove the specified material.
     */
    public function destroyMaterial(LessonMaterial $material)
    {
        if ($material->material_type !== 'link' && $material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return back()->with('success', 'Material deleted successfully.');
    }

    /**
     * Display a listing of assignments.
     */
    public function assignments()
    {
        $assignments = QueryBuilder::for(Assignment::class)
            ->with(['subject', 'rank', 'teacher:id,name'])
            ->withCount('submissions')
            ->allowedFilters(['subject_id', 'rank_id'])
            ->defaultSort('-created_at')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Admin/Lms/Assignments', [
            'assignments' => $assignments,
            'subjects' => Subject::active()->get(),
            'classes' => Rank::all(),
            'filters' => request()->all('subject_id', 'rank_id'),
        ]);
    }

    /**
     * Store a newly created assignment.
     */
    public function storeAssignment(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'rank_id' => 'required|exists:ranks,id',
            'instructions' => 'required|string',
            'due_date' => 'required|date|after:today',
            'max_points' => 'required|integer|min:0',
            'allow_late_submission' => 'boolean',
            'file' => 'nullable|file|max:10240', // 10MB
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('lms/assignments', 'public');
        }

        Assignment::create([
            'title' => $validated['title'],
            'subject_id' => $validated['subject_id'],
            'rank_id' => $validated['rank_id'],
            'teacher_id' => auth()->id(),
            'instructions' => $validated['instructions'],
            'due_date' => $validated['due_date'],
            'max_points' => $validated['max_points'],
            'allow_late_submission' => $validated['allow_late_submission'] ?? false,
            'attachment_path' => $filePath,
            'is_published' => true,
        ]);

        return back()->with('success', 'Assignment created successfully.');
    }

    /**
     * Remove the specified assignment.
     */
    public function destroyAssignment(Assignment $assignment)
    {
        if ($assignment->attachment_path) {
            Storage::disk('public')->delete($assignment->attachment_path);
        }

        $assignment->delete();

        return back()->with('success', 'Assignment deleted successfully.');
    }

    /**
     * Display a listing of online classes.
     */
    public function classes()
    {
        $classes = QueryBuilder::for(OnlineClass::class)
            ->with(['subject', 'rank', 'teacher:id,name'])
            ->allowedFilters(['subject_id', 'rank_id', 'status'])
            ->defaultSort('-scheduled_at')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Admin/Lms/Classes', [
            'onlineClasses' => $classes,
            'subjects' => Subject::active()->get(),
            'ranks' => Rank::all(),
            'filters' => request()->all('subject_id', 'rank_id', 'status'),
        ]);
    }

    /**
     * Store a newly created online class.
     */
    public function storeClass(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'rank_id' => 'required|exists:ranks,id',
            'description' => 'nullable|string',
            'meeting_link' => 'required|url',
            'meeting_platform' => 'required|in:zoom,google_meet,teams,other',
            'meeting_id' => 'nullable|string',
            'meeting_password' => 'nullable|string',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:5',
        ]);

        OnlineClass::create([
            ...$validated,
            'teacher_id' => auth()->id(),
            'status' => 'scheduled',
        ]);

        return back()->with('success', 'Online class scheduled successfully.');
    }

    /**
     * Display a listing of submissions for a specific assignment.
     */
    public function submissions(Assignment $assignment)
    {
        $submissions = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->with(['student:id,first_name,last_name,admission_number'])
            ->latest('submitted_at')
            ->get();

        return Inertia::render('Admin/Lms/Submissions', [
            'assignment' => $assignment->load(['subject', 'rank']),
            'submissions' => $submissions,
        ]);
    }

    /**
     * Grade a student submission.
     */
    public function gradeSubmission(Request $request, AssignmentSubmission $submission)
    {
        $validated = $request->validate([
            'points_earned' => 'required|integer|min:0',
            'teacher_feedback' => 'nullable|string',
        ]);

        $submission->update([
            'points_earned' => $validated['points_earned'],
            'teacher_feedback' => $validated['teacher_feedback'],
            'graded_by' => auth()->id(),
            'graded_at' => now(),
            'status' => 'graded',
        ]);

        return back()->with('success', 'Submission graded successfully.');
    }
}
