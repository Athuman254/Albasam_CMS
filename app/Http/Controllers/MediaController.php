<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\Website\Section;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function deleteLogo($institutionId)
    {
        $institution = Institution::findOrFail($institutionId);
        $institution->clearMediaCollection('logo');
        
        return to_route('institutions.index')->with('success', 'Institution logo deleted successfully.');
    }
    
    public function deleteFavicon($institutionId)
    {
        $institution = Institution::findOrFail($institutionId);
        $institution->clearMediaCollection('favicon');
        
        return to_route('institutions.index')->with('success', 'Institution favicon deleted successfully.');
    }
    
    public function uploadSectionMedia(Request $request)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'file' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);
        
        $section = Section::findOrFail($validated['section_id']);
        
        if($request->hasFile('file')) {
            $section->clearMediaCollection('section_image');
            $section->addMedia($validated['file'])
                ->toMediaCollection('section_image');
        }
        
        return back(303);
    }
    
    public function deleteSectionMedia($sectionId)
    {
        $section = Section::find($sectionId);
        $section->clearMediaCollection('section_image');
        
        return back(303);
    }
}
