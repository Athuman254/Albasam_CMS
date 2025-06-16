<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\MenuRequest;
use App\Http\Resources\Resource;
use App\Models\Website\Menu;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class MenuController extends Controller
{
    public function dataTable()
    {
        $menus = QueryBuilder::for(
            Menu::with('page')->orderBy('title')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::partial('title'),
        ])->jsonPaginate();
        
        return Resource::collection($menus);
    }
    
    public function index()
    {
        return Inertia::render('Admin/Website/Menus/Index', []);
    }
    
    public function store(MenuRequest $request)
    {
        $validated = $request->validated();
        
        Menu::create([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'page_id' => $validated['page_id'],
            'url' => $validated['url'],
            'order' => Menu::max('order') + 1,
            'has_children' => $validated['has_children'],
        ]);
        
        return to_route('menus.index')->with('success', 'Menu created successfully.');
    }
    
    public function update(MenuRequest $request, Menu $menu)
    {
        $validated = $request->validated();
        
        $menu->update([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'page_id' => $validated['page_id'],
            'url' => $validated['url'],
            'order' => $validated['order'],
            'has_children' => $validated['has_children'],
        ]);
        
        return to_route('menus.index')->with('success', 'Menu created successfully.');
    }
    
    public function destroy(Menu $menu)
    {
        $menu->delete();
        return to_route('menus.index')->with('success', 'Menu deleted successfully.');
    }
}
