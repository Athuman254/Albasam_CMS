<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\SeoMetaRequest;
use App\Http\Resources\Resource;
use App\Models\SeoMeta;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SeoMetaController extends Controller
{
    public function dataTable()
    {
        $metas = QueryBuilder::for(
            SeoMeta::orderBy('id')
        )->allowedFilters([
            AllowedFilter::partial('title'),
            AllowedFilter::exact('seo_able_type'),
            AllowedFilter::exact('seo_able_id'),
        ])->jsonPaginate();
        
        return Resource::collection($metas);
    }
    
    public function index()
    {
        return Inertia::render('Admin/Website/SeoMeta/Index', []);
    }
    
    public function store(SeoMetaRequest $request)
    {
        $validated = $request->validated();
        
//        dd($validated);
        
        SeoMeta::create($validated);
        
        return back(303)->with('success', 'SEO Meta has been created.');
    }
    
    public function update(SeoMetaRequest $request, SeoMeta $seoMeta)
    {
        $validated = $request->validated();
        
        $seoMeta->update($validated);
        
        return back(303)->with('success', 'SEO Meta has been updated.');
    }
    
    public function destroy(SeoMeta $seoMeta)
    {
        $seoMeta->delete();
        
        return back(303)->with('success', 'Meta SEO has been deleted.');
    }
}
