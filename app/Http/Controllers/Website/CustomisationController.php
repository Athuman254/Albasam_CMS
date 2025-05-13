<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomisationRequest;
use App\Models\Customisation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomisationController extends Controller
{
    public function dataTable()
    {
        return Customisation::orderBy('id')->first();
    }
    
    public function index()
    {
        return Inertia::render('Admin/Website/Customisation/Index', []);
    }
    
    public function store(CustomisationRequest $request)
    {
        $validated = $request->validated();
        
        Customisation::create([
            'primary_color' => $validated['primary_color'],
            'primary_color_rgb' => Customisation::hexToRgb($validated['primary_color']),
//            'primary_color_light' => $validated['primary_color_light'],
//            'primary_color_light_rgb' => Customisation::hexToRgb($validated['primary_color_light']),
//            'secondary_color' => $validated['secondary_color'],
//            'secondary_color_rgb' => Customisation::hexToRgb($validated['secondary_color']),
//            'secondary_color_light' => $validated['secondary_color_light'],
//            'secondary_color_light_rgb' => Customisation::hexToRgb($validated['secondary_color_light']),
        ]);
        
        return to_route('customisations.index');
    }
    
    public function update(CustomisationRequest $request, Customisation $customisation)
    {
        $validated = $request->validated();
        
        $customisation->update([
            'primary_color' => $validated['primary_color'],
            'primary_color_rgb' => Customisation::hexToRgb($validated['primary_color']),
//            'primary_color_light' => $validated['primary_color_light'],
//            'primary_color_light_rgb' => Customisation::hexToRgb($validated['primary_color_light']),
//            'secondary_color' => $validated['secondary_color'],
//            'secondary_color_rgb' => Customisation::hexToRgb($validated['secondary_color']),
//            'secondary_color_light' => $validated['secondary_color_light'],
//            'secondary_color_light_rgb' => Customisation::hexToRgb($validated['secondary_color_light']),
        ]);
        
        return to_route('customisations.index');
    }
    
    public function defaults(Customisation $customisation)
    {
        $customisation->update([
            'primary_color' => '#25615a',
//            'primary_color_light' => '#e1eefd',
//            'secondary_color' => '#ffd90d',
//            'secondary_color_light' => '#fff786',
        ]);
        
        return to_route('customisations.index');
    }
    
    public function setButtonStyles(Request $request, Customisation $customisation)
    {
        $request->validate([
            'button_style' => ['required']
        ]);
        
        $customisation->update([
            'button_style' => $request->button_style,
        ]);
        
        return to_route('customisations.index');
    }
}
