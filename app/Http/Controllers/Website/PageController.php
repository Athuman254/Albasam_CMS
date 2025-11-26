<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\Website\Page;
use App\Models\Website\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

        $slug = Str::slug($validated['title']);

        Page::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'],
            'published' => $validated['published'] ?? false,
            'is_home' => $isHome,
        ]);

        return back(303)->with('success', 'Page created.');
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
        
        $slug = Str::slug($validated['title']);

        $page->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'],
            'published' => $validated['published'] ?? false,
            'is_home' => $isHome,
        ]);

        return back(303)->with('success', 'Page updated successfully.');
    }
    
    public function destroy(Page $page)
    {
        foreach ($page->sections as $section) {
            $section->delete();
        }
        $page->delete();
        
        return back(303)->with('success', 'Page deleted successfully');
    }
    
    public function show($page)
    {
        $page = Page::where('slug', '=', $page)->firstOrFail();
        $sections = Section::with('cta_buttons.page', 'media')->where('page_id', '=', $page->id)->orderBy('order')->get() ?? null;
        $customisation = \App\Models\Website\Customisation::orderBy('id')->first() ?? null;
        
        $seo = $page->seoMeta;
        
        if($seo) {
            \App\Models\SeoMeta::applyMeta($seo);
        }
        
        return view('website.template-1.pages.show', [
            'page' => $page,
            'sections' => $sections,
            'customisation' => $customisation,
        ]);
    }
}
