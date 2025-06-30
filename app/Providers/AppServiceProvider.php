<?php

namespace App\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (Schema::hasTable('institutions')) {
            $institution = \App\Models\Institution::first(); // Fetch the first institution
            
            if ($institution) {
                $logo = $institution->hasMedia('logo') ? $institution->getMedia('logo')->sortByDesc('created_at')->first()->getUrl() : null;
                $favicon = $institution->hasMedia('favicon') ? $institution->getMedia('favicon')->sortByDesc('created_at')->first()->getUrl() : null;
                View::share([
                    'institution' => $institution,
                    'logo' => $logo,
                    'favicon' => $favicon,
                ]);
            }
        }
        if (Schema::hasTable('customisations')) {
            $customisation = \App\Models\Website\Customisation::orderBy('id')->first() ?? null;
            if ($customisation) {
                View::share([
                    'customisation' => $customisation,
                ]);
            }
        }
        if (Schema::hasTable('menus')) {
            $menus = \App\Models\Website\Menu::where('parent_id', '=', null)
                ->with('page', 'children')
                ->orderBy('order')
                ->get() ?? null;
            
            $menus?->load('page');
            
            View::share([
                'menus' => $menus,
            ]);
        }
        if(Schema::hasTable('pages')) {
            $pages = \App\Models\Website\Page::where('published', true)->where('is_home', false)->get();
            
            if ($pages) {
                View::share('pages', $pages);
            }
        }
        if (Schema::hasTable('blogs')) {
            $blogs = \App\Models\Website\Blog::orderByDesc('created_at')->get() ?? null;
            $blogs->load('user', 'category', 'media');
            
            if ($blogs) {
                View::share([
                    'blogs' => $blogs,
                ]);
            }
        }
        if (Schema::hasTable('careers')) {
            $careers = \App\Models\Website\Career::where('active', '=', true)->orderByDesc('created_at')->get() ?? null;
            $careers->load('user', 'contract_type');
            
            if ($careers) {
                View::share([
                    'careers' => $careers,
                ]);
            }
        }
        Inertia::share([
            'auth' => function () {
                return [
                    'user' => Auth::user() ? Auth::user()->only(['id', 'name', 'username', 'email', 'email_verified_at', 'phone', 'activated']) : null,
                ];
            },
            'employee' => function () {
                $employee = Auth::guard('employee')->user();
                return [
                    'staff' => $employee ? $employee->only(['id', 'first_name', 'last_name', 'email']) : null,
                ];
            }
        ]);
        $loader = AliasLoader::getInstance();
        $loader->alias('SEOTools', \Artesaos\SEOTools\Facades\SEOTools::class);
        
        Vite::prefetch(concurrency: 3);
    }
}
