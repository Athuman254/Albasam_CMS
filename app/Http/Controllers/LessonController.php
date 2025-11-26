<?php

namespace App\Http\Controllers;

use App\Http\Requests\LessonRequest;
use App\Http\Resources\Resource;
use App\Models\Rank;
use App\Models\Lesson;
use App\Rules\LessonTimeAvailabilityRule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LessonController extends Controller
{
    public function dataTable()
    {
        $lessons = QueryBuilder::for(
            Lesson::with('rank', 'subject', 'teacher.honorific')
                ->orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('activated'),
            AllowedFilter::exact('rank_id'),
            AllowedFilter::exact('subject_id'),
            AllowedFilter::exact('teacher_id'),
        ])->jsonPaginate();

        return Resource::collection($lessons);
    }
    
//    public function index()
//    {
//        return Inertia::render('Admin/Lessons/Index', []);
//    }

    public function store(LessonRequest $request)
    {
        $validated = $request->validated();

        Lesson::create([
            'rank_id' => $validated['rank_id'],
            'subject_id' => $validated['subject_id'],
            'teacher_id' => $validated['teacher_id'],
            'weekday' => $validated['weekday'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        return back(303)->with('success', 'Lesson created successfully.');
    }

    public function update(LessonRequest $request, Lesson $lesson)
    {
        $validated = $request->validated();

        $lesson->update([
            'rank_id' => $validated['rank_id'],
            'subject_id' => $validated['subject_id'],
            'teacher_id' => $validated['teacher_id'],
            'weekday' => $validated['weekday'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);
        
        return back(303)->with('success', 'Lesson updated successfully.');
    }

//    public function syncSubjects(Request $request)
//    {
//        $validated = $request->validate([
//            'rank_id' => ['required', Rule::exists('ranks', 'id')],
//            'subjects' => ['nullable', 'array'],
//            'subjects.*.subject_id' => [Rule::exists('subjects', 'id')],
//        ]);
//
//        $rank = Rank::findOrFail($validated['rank_id']);
//        $rank->subjects()->sync($validated['subjects']);
//
//        return to_route('ranks.show', $rank->hashid);
//    }

    public function destroy(Lesson $lesson)
    {
        $lesson->delete();

        return response()->noContent();
    }
}
