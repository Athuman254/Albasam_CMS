<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\MenuRequest;
use App\Http\Resources\Resource;
use App\Models\Website\Menu;
use App\Models\Website\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class MenuController extends Controller
{
    public function dataTable()
    {
        $menus = QueryBuilder::for(
            Menu::with('page', 'parent', 'children')
                ->where('parent_id', '=', null)
                ->orderBy('title')
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
        
        DB::beginTransaction();
        
        try {
            $menu = Menu::create([
                'title' => $validated['title'],
                'type' => $validated['type'],
                'page_id' => $validated['page_id'],
                'url' => $validated['url'],
                'order' => Menu::max('order') + 1,
                'has_children' => $validated['has_children'],
                'child_type' => $validated['child_type'],
                'component' => $validated['component'],
            ]);
            
            if ($validated['has_children'] && isset($validated['children']) && is_array($validated['children'])) {
                foreach ($validated['children'] as $child) {
                    $childPage = Page::find($child);
                    Menu::create([
                        'title' => $childPage->title,
                        'parent_id' => $menu->id,
                        'page_id' => $childPage->id,
                        'type' => Menu::TYPE_PAGE,
                        'url' => null,
                        'order' => 0,
                        'has_children' => false,
                    ]);
                }
            }
            
            DB::commit();
            return back(303);
            
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Error: ' . $exception->getMessage());
            report($exception);
            return redirect()->back()->withInput()->withErrors(['message' => $exception->getMessage()]);
        }
    }
    
    public function update(MenuRequest $request, Menu $menu)
    {
        $validated = $request->validated();
        
        DB::beginTransaction();
        
        try {
            $menu->update([
                'title' => $validated['title'],
                'type' => $validated['type'],
                'page_id' => $validated['page_id'],
                'url' => $validated['url'],
                'order' => $validated['order'],
                'has_children' => $validated['has_children'],
                'child_type' => $validated['child_type'],
                'component' => $validated['component'],
            ]);
            
            if(!$validated['has_children'] && $menu->children()->count() > 0) {
                $menu->children()->delete();
            }
            
            if ($validated['has_children'] && isset($validated['children']) && is_array($validated['children'])) {
                $menu->children()->delete();
                foreach ($validated['children'] as $child) {
                    $childPage = Page::find($child);
                    Menu::create([
                        'title' => $childPage->title,
                        'parent_id' => $menu->id,
                        'page_id' => $childPage->id,
                        'type' => Menu::TYPE_PAGE,
                        'url' => null,
                        'order' => 0,
                        'has_children' => false,
                    ]);
                }
            }
            
            DB::commit();
            return back(303);
            
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Error: ' . $exception->getMessage());
            report($exception);
            return redirect()->back()->withInput()->withErrors(['message' => $exception->getMessage()]);
        }
    }
    
    public function destroy(Menu $menu)
    {
        if ($menu->children()->count() > 0) {
            $menu->children()->delete();
        }
        $menu->delete();
        return to_route('admin.menus.index')->with('success', 'Menu deleted successfully.');
    }
}
