<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\Website\Page;
use App\Models\Website\Section;
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
            AllowedFilter::exact('published'),
            AllowedFilter::partial('title'),
        ])->jsonPaginate();

        return Resource::collection($pages);
    }

    public function index()
    {
        return Inertia::render('Admin/Website/Pages/Index', []);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|unique:pages,title',
            'description' => 'nullable|string',
            'slug' => 'nullable|string',
            'published' => 'boolean',
        ]);
        
        $isHome = false;
        if ($validated['title'] == 'Home') {
            $isHome = true;
        }

        $slug = strtolower(str_replace(' ', '-', $validated['title']));

        Page::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'],
            'published' => $validated['published'] ?? false,
            'is_home' => $isHome,
        ]);

        return to_route('pages.index')->with('success', 'Page created.');
    }
    
    public function show($page)
    {
        $page = Page::where('slug', '=', $page)->firstOrFail();
        $sections = Section::with('cta_buttons.page', 'media')->where('page_id', '=', $page->id)->orderBy('order')->get() ?? null;
        $customisation = \App\Models\Website\Customisation::orderBy('id')->first() ?? null;
        
        return view('website.template-1.pages.show', [
            'page' => $page,
            'sections' => $sections,
            'customisation' => $customisation,
        ]);
    }
    
    public function manageSections(Page $page)
    {
        return Inertia::render('Admin/Website/Sections/ManagePageSections', [
            'page' => $page,
        ]);
    }

    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|unique:pages,title,' . $page->id,
            'slug' => 'nullable|string|unique:pages,slug,'.$page->id,
            'description' => 'nullable|string',
            'published' => 'boolean',
        ]);
        
        $isHome = false;
        
        if ($validated['title'] == 'Home' || $validated['slug'] == 'home') {
            $isHome = true;
        }

        $slug = strtolower(str_replace(' ', '-', $validated['slug']));

        $page->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'],
            'published' => $validated['published'] ?? false,
            'is_home' => $isHome,
        ]);

        return to_route('pages.index')->with('success', 'Page updated successfully.');
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return to_route('pages.index')->with('success', 'Page deleted');
    }
}
