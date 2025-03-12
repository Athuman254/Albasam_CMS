<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DivisionController extends Controller
{
    public function dataTable()
    {
        $divisions = QueryBuilder::for(
            Division::orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($divisions);
    }

    public function index()
    {
        return Inertia::render('Admin/Configurations/Divisions/Index', []);
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

        return to_route('divisions.index')->with('success', 'Division created.');
    }

    public function update(Division $division, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255'],
            'activated' => ['required', 'boolean'],
        ]);

        $division->update([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);

        return to_route('divisions.index')->with('success', 'Division updated.');
    }
}
