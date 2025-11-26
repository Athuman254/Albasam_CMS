<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DivisionController extends Controller
{
    public function dataTable()
    {
        $divisions = QueryBuilder::for(
            Division::orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($divisions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','max:255', Rule::unique('divisions', 'name')],
            'activated' => ['required','boolean'],
        ]);

        Division::create([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);

        return back(303)->with('success', 'Division created.');
    }

    public function update(Division $division, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('divisions', 'name')->ignore($division->id)],
            'activated' => ['required', 'boolean'],
        ]);

        $division->update([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);

        return back(303)->with('success', 'Division updated.');
    }
}
