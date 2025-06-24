<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LanguageController extends Controller
{
    public function dataTable()
    {
        $categories = QueryBuilder::for(
            Language::orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();
        
        return Resource::collection($categories);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','max:255', Rule::unique('languages', 'name')],
            'activated' => ['required','boolean'],
        ]);
        
        Language::create([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Language created.');
    }
    
    public function update(Language $career_category, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('languages', 'name')->ignore($career_category)],
            'activated' => ['required', 'boolean'],
        ]);
        
        $career_category->update([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Category updated.');
    }
}
