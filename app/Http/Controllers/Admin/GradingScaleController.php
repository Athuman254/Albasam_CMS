<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\GradingScale;
use App\Models\GradingEntry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Inertia\Inertia;

class GradingScaleController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/GradingScales/Index');
    }

    public function dataTable()
    {
        $scales = QueryBuilder::for(
            GradingScale::withCount('entries')->orderBy('name')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('is_active'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($scales);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('grading_scales', 'name')],
            'description' => ['nullable', 'max:255'],
            'is_default' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($validated['is_default']) {
            GradingScale::where('is_default', true)->update(['is_default' => false]);
        }

        GradingScale::create($validated);

        return back(303)->with('success', 'Grading scale created.');
    }

    public function update(GradingScale $gradingScale, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('grading_scales', 'name')->ignore($gradingScale->id)],
            'description' => ['nullable', 'max:255'],
            'is_default' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($validated['is_default'] && !$gradingScale->is_default) {
            GradingScale::where('is_default', true)->update(['is_default' => false]);
        }

        $gradingScale->update($validated);

        return back(303)->with('success', 'Grading scale updated.');
    }

    public function destroy(GradingScale $gradingScale)
    {
        if ($gradingScale->is_default) {
            return back()->with('error', 'Cannot delete the default grading scale.');
        }

        $gradingScale->delete();
        return back()->with('success', 'Grading scale deleted.');
    }

    /**
     * Get entries for a specific scale
     */
    public function getEntries(GradingScale $gradingScale)
    {
        $entries = $gradingScale->entries()->get();
        return response()->json($entries);
    }

    /**
     * Store entries for a scale (sync)
     */
    public function syncEntries(Request $request, GradingScale $gradingScale)
    {
        $validated = $request->validate([
            'entries' => 'required|array',
            'entries.*.grade' => 'required|string|max:5',
            'entries.*.min_score' => 'required|numeric|min:0|max:100',
            'entries.*.max_score' => 'required|numeric|min:0|max:100',
            'entries.*.points' => 'required|integer|min:0',
            'entries.*.remarks' => 'nullable|string|max:255',
        ]);

        $gradingScale->entries()->delete();

        foreach ($validated['entries'] as $entryData) {
            $gradingScale->entries()->create($entryData);
        }

        return back()->with('success', 'Entries updated successfully');
    }
}
