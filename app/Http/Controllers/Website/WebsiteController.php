<?php

namespace App\Http\Controllers\Website;
use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\Page;

class WebsiteController extends Controller
{
   public function index(): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application
   {
      $homePage = Page::where('is_home', '=', true)
         ->where('published', '=', true)->firstOrFail();
      
      return view('website.template-1.pages.home', [
           'homePage' => $homePage,
      ]);
   }
   
   public function page($slug)
   {
      $page = Page::where('slug', '=', $slug)
         ->where('is_home', '=', false)
         ->where('published', '=', true)
         ->firstOrFail();
   
      return view('website.template-1.pages.page_data', [
         'page' => $page,
      ]);
   }
}
