<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\Honorific;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class HonorificController extends Controller
{
    public function dataTable()
    {
        $honorifics = QueryBuilder::for(
            Honorific::orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($honorifics);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('honorifics', 'name')],
            'activated' => ['required','boolean'],
        ]);
        
        Honorific::create([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Honorific created.');
    }
    
    public function update(Honorific $honorific, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('honorifics', 'name')->ignore($honorific->id)],
            'activated' => ['required','boolean'],
        ]);
        
        $honorific->update([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Honorific details updated.');
    }
}
