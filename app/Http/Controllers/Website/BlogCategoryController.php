<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\Website\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BlogCategoryController extends Controller
{
    public function dataTable()
    {
        $categories = QueryBuilder::for(
            BlogCategory::orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();
        
        return Resource::collection($categories);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','max:255', Rule::unique('blog_categories', 'name')],
            'activated' => ['required','boolean'],
        ]);
        
        BlogCategory::create([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Category created.');
    }
    
    public function update(BlogCategory $blog_category, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('blog_categories', 'name')->ignore($blog_category)],
            'activated' => ['required', 'boolean'],
        ]);
        
        $blog_category->update([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Category updated.');
    }
}
