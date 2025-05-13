<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
}
