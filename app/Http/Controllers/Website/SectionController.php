<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\Page;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SectionController extends Controller
{
    public function dataTable()
    {
        $pages = QueryBuilder::for(
            Section::with('subSections', 'page')->orderBy('order')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('page_id'),
            AllowedFilter::partial('title'),
        ])->jsonPaginate();

        return Resource::collection($pages);
    }

    public function index()
    {
        return Inertia('admin/Website/Sections/Index');
    }

    public function create(Page $page)
    {
        return Inertia::render('admin/Website/Sections/Create', [
            'page' => $page,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sections' => 'required|array|min:1',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.sub_title' => 'nullable|string|max:255',
            'sections.*.order' => 'required|integer',
            'sections.*.bg_style' => 'required',
            'sections.*.bg_color' => 'nullable',
            'sections.*.bg_image' => 'nullable',
            'sections.*.type' => 'nullable|integer|in:1,2',
            'sections.*.content' => 'nullable',
            'sections.*.type_image' => 'nullable',
            'sections.*.subSections' => 'nullable|array',
            'sections.*.subSections.*.title' => 'required|string|max:255',
            'sections.*.subSections.*.sub_title' => 'nullable|string|max:255',
            'sections.*.subSections.*.order' => 'required|integer',
            'sections.*.subSections.*.type' => 'required|integer|in:1,2',
            'sections.*.subSections.*.content' => 'nullable|string',
            'sections.*.subSections.*.type_image' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->sections as $sectionData) {
                $section = Section::create([
                    'title' => $sectionData['title'],
                    'sub_title' => $sectionData['sub_title'],
                    'order' => $sectionData['order'],
                    'bg_style' => $sectionData['bg_style'],
                    'bg_color' => $sectionData['bg_color'],
                    'bg_image' => $sectionData['bg_image'],
                    'type' => $sectionData['type'],
                    'content' => $sectionData['content'] ?? '',
                    'type_image' => $sectionData['type_image'] ?? '',
                    'page_id' => $request->page_id, // Assuming sections belong to a page
                ]);

                if (!empty($sectionData['subSections'])) {
                    foreach ($sectionData['subSections'] as $subSectionData) {
                        $section->subSections()->create([
                            'title' => $subSectionData['title'],
                            'sub_title' => $subSectionData['sub_title'],
                            'order' => $subSectionData['order'],
                            'type' => $subSectionData['type'],
                            'content' => $subSectionData['content'] ?? '',
                            'type_image' => $subSectionData['type_image'] ?? '',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            return to_route('pages.index')->with('success', 'Page sections created successfully.');

        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Error: ' . $exception->getMessage());
            report($exception);
            return to_route('pages.sections.create')->with('error', $exception->getMessage());
        }
    }

    public function edit(Page $page)
    {
        $page->load('sections.subSections');
        return Inertia::render('admin/Website/Sections/Edit', [
            'page' => $page,
        ]);
    }

    public function update(Page $page, Request $request)
    {
        $validated = $request->validate([
            'sections' => 'required|array|min:1',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.sub_title' => 'nullable|string|max:255',
            'sections.*.order' => 'required|integer',
            'sections.*.bg_style' => 'required',
            'sections.*.bg_color' => 'nullable',
            'sections.*.bg_image' => 'nullable',
            'sections.*.type' => 'nullable|integer|in:1,2',
            'sections.*.content' => 'nullable',
            'sections.*.type_image' => 'nullable',
            'sections.*.subSections' => 'nullable|array',
            'sections.*.subSections.*.title' => 'required|string|max:255',
            'sections.*.subSections.*.sub_title' => 'nullable|string|max:255',
            'sections.*.subSections.*.order' => 'required|integer',
            'sections.*.subSections.*.type' => 'required|integer|in:1,2',
            'sections.*.subSections.*.content' => 'nullable|string',
            'sections.*.subSections.*.type_image' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Get existing section IDs for this page
            $existingSectionIds = $page->sections()->pluck('id')->toArray();
            $newSectionIds = [];

            foreach ($validated['sections'] as $sectionData) {
                // Update existing section or create new one
                $section = Section::updateOrCreate(
                    ['page_id' => $page->id, 'order' => $sectionData['order']], // Unique identifier
                    [
                        'title' => $sectionData['title'],
                        'sub_title' => $sectionData['sub_title'],
                        'bg_style' => $sectionData['bg_style'],
                        'bg_color' => $sectionData['bg_color'],
                        'bg_image' => $sectionData['bg_image'],
                        'type' => $sectionData['type'],
                        'content' => $sectionData['content'] ?? '',
                        'type_image' => $sectionData['type_image'] ?? '',
                    ]
                );

                $newSectionIds[] = $section->id;

                // Process subsections
                $existingSubSectionIds = $section->subSections()->pluck('id')->toArray();
                $newSubSectionIds = [];

                if (!empty($sectionData['subSections'])) {
                    foreach ($sectionData['subSections'] as $subSectionData) {
                        $subSection = $section->subSections()->updateOrCreate(
                            ['section_id' => $section->id, 'order' => $subSectionData['order']], // Unique identifier
                            [
                                'title' => $subSectionData['title'],
                                'sub_title' => $subSectionData['sub_title'] ?? null,
                                'type' => $subSectionData['type'],
                                'content' => $subSectionData['content'] ?? '',
                                'type_image' => $subSectionData['type_image'] ?? '',
                            ]
                        );
                        $newSubSectionIds[] = $subSection->id;
                    }
                }

                // Delete removed subsections
                $section->subSections()->whereNotIn('id', $newSubSectionIds)->delete();
            }

            // Delete removed sections
            Section::where('page_id', '=', $page->id)->whereNotIn('id', $newSectionIds)->each(function ($section) {
                $section->subSections()->delete(); // Delete associated subsections first
                $section->delete();
            });

            DB::commit();
            return to_route('pages.index')->with('success', 'Page sections updated successfully.');

        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Error updating page sections: ' . $exception->getMessage());
            return redirect()->back()->withInput()->withErrors(['message' => 'Failed to update page sections. Please try again.']);
        }
    }

}
