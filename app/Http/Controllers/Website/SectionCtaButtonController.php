<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\Website\SectionCtaButton;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SectionCtaButtonController extends Controller
{
    public function datatable()
    {
        $ctaButtons = QueryBuilder::for(
            SectionCtaButton::with('section', 'page')->orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('section_id'),
            AllowedFilter::exact('page_id'),
        ])->jsonPaginate();
        
        return Resource::collection($ctaButtons);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'section_id' => ['required', Rule::exists('sections', 'id')],
            'page_id' => ['required', Rule::exists('pages', 'id')],
            'cta_button_text' => ['required', 'string'],
            'cta_button_type' => ['required', 'string'],
        ]);
        
        SectionCtaButton::create($validated);
        
        return back(303);
    }
    
    public function update(Request $request, SectionCtaButton $sectionCtaButton)
    {
        $validated = $request->validate([
            'section_id' => ['required', Rule::exists('sections', 'id')],
            'page_id' => ['required', Rule::exists('pages', 'id')],
            'cta_text' => ['required', 'string'],
        ]);
        
        $sectionCtaButton->update($validated);
        
        return back(303);
    }
    
    public function destroy($sectionCtaButtonId)
    {
//        dd($sectionCtaButtonId);
        $sectionCtaButton = SectionCtaButton::findOrFail($sectionCtaButtonId);
        $sectionCtaButton->delete();
        
        return back(303);
    }
}
