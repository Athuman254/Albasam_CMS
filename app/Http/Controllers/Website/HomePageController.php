<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomePageController extends Controller
{
    public function index()
    {
        $homePage = \App\Models\Website\Page::where('title', '=', 'Home')->firstOrFail();
        $customisation = \App\Models\Website\Customisation::orderBy('id')->first() ?? null;
        
        $seo = $homePage->seoMeta;
        
        if($seo) {
            \App\Models\SeoMeta::applyMeta($seo);
        }
        
        if ($homePage) {
            $homePage->load('sections.cta_buttons.page');
            $sections = \App\Models\Website\Section::with('cta_buttons.page', 'media')->where('page_id', '=', $homePage->id)->get() ?? null;
            
            return view('website.template-1.pages.home', [
                'page' => $homePage,
                'sections' => $sections,
                'customisation' => $customisation,
            ]);
        }
        
        return view('website.template-1.landing-page');
    }
}
