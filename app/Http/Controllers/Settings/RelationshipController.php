<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\Relationship;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class RelationshipController extends Controller
{
    public function dataTable()
    {
        $relationships = QueryBuilder::for(
            Relationship::orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($relationships);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','max:255', Rule::unique('relationships', 'name')],
            'activated' => ['required','boolean'],
        ]);
        
        Relationship::create([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Relationship created.');
    }
    
    public function update(Relationship $relationship, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('relationships', 'name')->ignore($relationship->id)],
            'activated' => ['required', 'boolean'],
        ]);
        
        $relationship->update([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Relationship updated.');
    }
}
