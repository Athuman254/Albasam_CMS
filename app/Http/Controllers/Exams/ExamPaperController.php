<?php

namespace App\Http\Controllers\Exams;

use App\Http\Controllers\Controller;
use App\Models\ExamPaper;
use App\Models\ExamSubject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExamPaperController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        $papers = ExamPaper::with(['examSubject.exam', 'examSubject.subject', 'examSubject.class'])
            ->unless($user->hasRole('admin'), function ($query) use ($user) {
                $query->where('teacher_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Exams/Papers/Index', [
            'papers' => $papers
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();

        // Get available exam subjects for this teacher or all if admin
        $examSubjects = ExamSubject::with(['exam', 'subject', 'class'])
            ->orderBy('exam_id', 'desc')
            ->get();

        return Inertia::render('Exams/Papers/Create', [
            'examSubjects' => $examSubjects
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_subject_id' => 'required|exists:exam_subjects,id',
            'title' => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'status' => 'required|string|in:draft,submitted,approved',
        ]);

        $validated['teacher_id'] = auth()->id();

        ExamPaper::create($validated);

        return redirect()->route('exams.papers.index')->with('success', 'Exam paper created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ExamPaper $examPaper)
    {
        $examPaper->load(['examSubject.exam', 'examSubject.subject', 'examSubject.class', 'teacher']);
        return Inertia::render('Exams/Papers/Show', [
            'paper' => $examPaper
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExamPaper $examPaper)
    {
        $examPaper->load('examSubject');
        $examSubjects = ExamSubject::with(['exam', 'subject', 'class'])->get();

        return Inertia::render('Exams/Papers/Edit', [
            'paper' => $examPaper,
            'examSubjects' => $examSubjects
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExamPaper $examPaper)
    {
        $validated = $request->validate([
            'exam_subject_id' => 'required|exists:exam_subjects,id',
            'title' => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'status' => 'required|string|in:draft,submitted,approved',
        ]);

        $examPaper->update($validated);

        return redirect()->route('exams.papers.index')->with('success', 'Exam paper updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExamPaper $examPaper)
    {
        $examPaper->delete();
        return redirect()->route('exams.papers.index')->with('success', 'Exam paper deleted successfully.');
    }
}
