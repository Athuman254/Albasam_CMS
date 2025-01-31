<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\Page;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PageController extends Controller
{
    public function dataTable()
    {
        $pages = QueryBuilder::for(
            Page::with('sections')->orderBy('title')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::partial('title'),
        ])->jsonPaginate();

        return Resource::collection($pages);
    }

    public function index()
    {
        return Inertia::render('Website/Pages/Index', []);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|unique:pages,title',
            'content' => 'nullable|string',
            'is_published' => 'boolean',
        ]);

        $slug = strtolower(str_replace(' ', '-', $request->title));

        Page::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'content' => $validated['content'],
            'is_published' => $validated['is_published'] ?? false,
        ]);

        return to_route('pages.index')->with('success', 'Page created.');
    }

    public function update(Request $request, $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|unique:pages,title,' . $page,
            'slug' => 'required|string|unique:pages,slug,'.$page,
            'content' => 'nullable|string',
            'is_published' => 'boolean',
        ]);

        $page = Page::findOrFail($page);
        $page->update([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'content' => $validated['content'],
            'is_published' => $validated['is_published'] ?? false,
        ]);

        return to_route('pages.index')->with('success', 'Page updated successfully.');
    }

    public function destroy($page)
    {
        $page = Page::findOrFail($page);
        $page->delete();
        return to_route('pages.index')->with('success', 'Page deleted');
    }
}
