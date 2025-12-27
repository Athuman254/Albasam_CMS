<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\Rank;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Facades\Log;

class RankController extends Controller
{
    public function dataTable()
    {
        \Log::info('RankController::dataTable called');

        $classes = QueryBuilder::for(
            Rank::with(['division', 'stream', 'teacher.honorific'])
                ->withCount('students')
                ->orderBy('name')
        )->allowedFilters([
            AllowedFilter::exact('activated'),
            AllowedFilter::exact('division_id'),
            AllowedFilter::exact('stream_id'),
            AllowedFilter::partial('name'),
            AllowedFilter::scope('search', 'Search'),
        ])->jsonPaginate();

        \Log::info('RankController found classes: ' . $classes->count());

        return Resource::collection($classes);
    }

    public function index()
    {
        return Inertia::render('Admin/Classes/Index', []);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255'],
            'division_id' => ['nullable', Rule::exists('divisions', 'id')],
            'stream_id' => ['nullable', Rule::exists('streams', 'id')],
            'teacher_id' => ['nullable', Rule::exists('teachers', 'id')],
            'activated' => ['boolean'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        Rank::create([
            'name' => $validated['name'],
            'division_id' => $validated['division_id'],
            'stream_id' => $validated['stream_id'],
            'teacher_id' => $validated['teacher_id'],
            'activated' => $validated['activated'],
            'capacity' => $validated['capacity'] ?? null,
        ]);

        return back(303)->with('success', 'Class created.');
    }

    public function show(Rank $rank)
    {
        $rank->load('division', 'stream', 'teacher.honorific');

        return Inertia::render('Admin/Classes/Show', [
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
            'capacity' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $rank->update([
            'name' => $validated['name'],
            'division_id' => $validated['division_id'],
            'stream_id' => $validated['stream_id'],
            'teacher_id' => $validated['teacher_id'],
            'activated' => $validated['activated'],
            'capacity' => $validated['capacity'] ?? null,
        ]);

        return back(303)->with('success', 'Class details updated.');
    }
}
