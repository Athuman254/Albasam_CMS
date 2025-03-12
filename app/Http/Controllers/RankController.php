<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\Rank;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class RankController extends Controller
{
    public function dataTable()
    {
        $classes = QueryBuilder::for(
            Rank::with(['division', 'stream', 'teacher.honorific'])->orderBy('name')
        )->allowedFilters([
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($classes);
    }

    public function index()
    {
        return Inertia::render('admin/Classes/Index', []);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255'],
            'division_id' => ['nullable', Rule::exists('divisions', 'id')],
            'stream_id' => ['nullable', Rule::exists('streams', 'id')],
            'teacher_id' => ['nullable', Rule::exists('teachers', 'id')],
            'activated' => ['boolean'],
        ]);
        // dd($validated);
        Rank::create([
            'name' => $validated['name'],
            'division_id' => $validated['division_id'],
            'stream_id' => $validated['stream_id'],
            'teacher_id' => $validated['teacher_id'],
            'activated' => $validated['activated'],
        ]);

        return to_route('ranks.index')->with('success', 'Class created.');
    }
    
    public function show(Rank $rank)
    {
        $rank->load('division', 'stream', 'teacher.honorific');
        
        return Inertia::render('admin/Classes/Show', [
            'rank' => $rank,
        ]);
    }

    public function update(Rank $rank, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255'],
            'division_id' => ['nullable', Rule::exists('divisions', 'id')],
            'stream_id' => ['nullable', Rule::exists('streams', 'id')],
            'teacher_id' => ['nullable', Rule::exists('teachers', 'id')],
            'activated' => ['boolean'],
        ]);

        $rank->update([
            'name' => $validated['name'],
            'division_id' => $validated['division_id'],
            'stream_id' => $validated['stream_id'],
            'teacher_id' => $validated['teacher_id'],
            'activated' => $validated['activated'],
        ]);

        return to_route('ranks.index')->with('success', 'Class details updated.');
    }
}
