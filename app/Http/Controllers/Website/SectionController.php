<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\SectionRequest;
use App\Http\Resources\Resource;
use App\Models\Website\Page;
use App\Models\Website\Section;
use App\Models\Website\SectionCtaButton;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SectionController extends Controller
{
    public function datatable()
    {
        $sections = QueryBuilder::for(
            Section::with('page', 'cta_buttons.page', 'media')->orderBy('order')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('page_id'),
        ])->jsonPaginate();
        
        return Resource::collection($sections);
    }
    
    public function index()
    {
        return Inertia::render('Admin/Section/Index', []);
    }
    
    public function create(Page $page)
    {
        return Inertia::render('Admin/Website/Sections/Create', [
            'page' => $page,
        ]);
    }
    
    public function store(SectionRequest $request)
    {
        $validated = $request->validated();
        
        DB::beginTransaction();
        
        try {
            $section = Section::create([
                'page_id' => $validated['page_id'],
                'type' => $validated['type'],
                'title' => $validated['title'],
                'sub_title' => $validated['sub_title'],
                'component_type' => $validated['component_type'],
                'details' => $validated['details'],
                'include_contact_cards' => $validated['include_contact_cards'],
                'section_has_image' => $validated['section_has_image'],
                'section_image_first' => $validated['section_image_first'],
                'has_cta_buttons' => $validated['has_cta_buttons'],
                'map_link' => $validated['map_link'],
            ]);
            
            if($request->hasFile('media')) {
                $section->clearMediaCollection('section_image');
                $section->addMedia($validated['media'])->toMediaCollection('section_image');
            }
            
            if (isset($validated['cta_buttons']) && is_array($validated['cta_buttons'])) {
                $ctaButtons = collect($validated['cta_buttons'])
                    ->filter(function ($ctaButton) {
                        return isset($ctaButton['page']['id']);
                    })
                    ->map(function ($ctaButton) use ($section) {
                        return [
                            'page_id' => $ctaButton['page']['id'],
                            'section_id' => $section->id,
                            'cta_button_text' => $ctaButton['cta_button_text'],
                            'cta_button_type' => $ctaButton['cta_button_type']['value'],
                            'created_at' => now()->toDateTimeString(),
                            'updated_at' => now()->toDateTimeString(),
                        ];
                    })->toArray();
                
                if(!empty($ctaButtons)) {
                    SectionCtaButton::insert($ctaButtons);
                }
            }
            
            DB::commit();
            return back(303);
            
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Error: ' . $exception->getMessage());
            report($exception);
            return redirect()->back()->withInput()->withErrors(['message' => 'Failed to save page sections. Please try again.']);
        }
    }
    
    public function edit(Page $page)
    {
        return Inertia::render('Admin/Website/Sections/Edit', [
            'page' => $page,
        ]);
    }
    
    public function update(SectionRequest $request, $sectionId)
    {
        $validated = $request->validated();
        $section = Section::findOrFail($sectionId);
        if($validated['include_contact_cards']) {
            $validated['section_has_image'] = false;
            $validated['details'] = null;
        }
        
        DB::beginTransaction();
        
        try {
            $section->update([
                'type' => $validated['type'],
                'title' => $validated['title'],
                'sub_title' => $validated['sub_title'],
                'component_type' => $validated['component_type'],
                'details' => $validated['details'],
                'include_contact_cards' => $validated['include_contact_cards'],
                'section_has_image' => $validated['section_has_image'],
                'section_image_first' => $validated['section_image_first'],
                'has_cta_buttons' => $validated['has_cta_buttons'],
                'map_link' => $validated['map_link'],
            ]);
            
            if($validated['has_cta_buttons'] === false) {
                $section->cta_buttons()->delete();
            }
            if($validated['section_has_image'] === false) {
                $section->clearMediaCollection('section_image');
            }
            
            DB::commit();
            return back(303);
            
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Error: ' . $exception->getMessage());
            report($exception);
            return redirect()->back()->withInput()->withErrors(['message' => 'Failed to save page sections. Please try again.']);
        }
    }
    
    public function destroy($sectionId)
    {
        $section = Section::findOrFail($sectionId);
        $section->cta_buttons()->delete();
        $section->clearMediaCollection('section_image');
        
        $section->delete();
        
        return back(303);
    }

}
