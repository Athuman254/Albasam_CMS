<?php

namespace App\Http\Controllers\Website;
use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\Page;

class WebsiteController extends Controller
{
    public function index(): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application
    {
        $institution = Institution::orderBy('id')->first() ?? new Institution();

        return view('website.template-1.pages.home', []);
    }

    public function show($slug)
    {
        $page = Page::where('slug', $slug)
            ->where('is_published', '=', true)
            ->with(['sections' => function ($query) {
                $query->whereNull('parent_id')->where('is_active', true)->orderBy('order')
                    ->with(['children' => function ($subQuery) {
                        $subQuery->where('is_active', true)->orderBy('order');
                    }]);
            }])
            ->firstOrFail();

        return view('website.template-1.pages.page_data', [
            'page' => $page,
        ]);
    }
}
