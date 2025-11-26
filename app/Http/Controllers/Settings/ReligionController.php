<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\Religion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ReligionController extends Controller
{
    public function dataTable()
    {
        $religions = QueryBuilder::for(
            Religion::orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($religions);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','max:255', Rule::unique('religions', 'name')],
            'activated' => ['required','boolean'],
        ]);
        
        Religion::create([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Religion created.');
    }
    
    public function update(Religion $religion, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('religions', 'name')->ignore($religion)],
            'activated' => ['required', 'boolean'],
        ]);
        
        $religion->update([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Religion updated.');
    }
}
