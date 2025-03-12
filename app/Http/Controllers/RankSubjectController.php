<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\Rank;
use App\Models\RankSubject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class RankSubjectController extends Controller
{
    public function dataTable()
    {
        $rankSubjects = QueryBuilder::for(
            RankSubject::with('rank', 'subject', 'teacher.honorific')
                ->orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('rank_id'),
            AllowedFilter::exact('subject_id'),
            AllowedFilter::exact('teacher_id'),
        ])->jsonPaginate();
        
        return Resource::collection($rankSubjects);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rank_id' => ['required', Rule::exists('ranks', 'id')],
            'subject_id' => ['required', Rule::exists('subjects', 'id')],
            'teacher_id' => ['nullable', Rule::exists('teachers', 'id')],
        ]);
        
        $rank = Rank::findOrFail($validated['rank_id']);
        
        RankSubject::create([
            'rank_id' => $validated['rank_id'],
            'subject_id' => $validated['subject_id'],
            'teacher_id' => $validated['teacher_id'],
        ]);
        
        return to_route('ranks.show', $rank->hashid);
    }
    
    public function update(Request $request, RankSubject $rank_subject)
    {
        $validated = $request->validate([
            'rank_id' => ['required', Rule::exists('ranks', 'id')],
            'subject_id' => ['required', Rule::exists('subjects', 'id')],
            'teacher_id' => ['nullable', Rule::exists('teachers', 'id')],
        ]);
        
        $rank = Rank::findOrFail($validated['rank_id']);
        
        $rank_subject::update([
            'rank_id' => $validated['rank_id'],
            'subject_id' => $validated['subject_id'],
            'teacher_id' => $validated['teacher_id'],
        ]);
        
        return to_route('ranks.show', $rank->hashid);
    }
    
    public function syncSubjects(Request $request)
    {
        $validated = $request->validate([
            'rank_id' => ['required', Rule::exists('ranks', 'id')],
            'subjects' => ['nullable', 'array'],
            'subjects.*.subject_id' => [Rule::exists('subjects', 'id')],
        ]);
        
        $rank = Rank::findOrFail($validated['rank_id']);
        $rank->subjects()->sync($validated['subjects']);
        
        return to_route('ranks.show', $rank->hashid);
    }
    
    public function destroy(RankSubject $rank_subject)
    {
        $rank_subject->delete();
        
        return response()->noContent();
    }
}
