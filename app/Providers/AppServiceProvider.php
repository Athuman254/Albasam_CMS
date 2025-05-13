<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
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
            $customisation = \App\Models\Customisation::orderBy('id')->first() ?? null;
            if ($customisation) {
                View::share([
                    'customisation' => $customisation,
                ]);
            }
        }
        if (Schema::hasTable('menus')) {
            $menus = \App\Models\Menu::whereHas('page', function($query) {
                $query->where('published', '=', true);
            })
                ->with('page')
                ->orderBy('order')
                ->get() ?? null;
            
            $menus?->load('page');
            
            View::share([
                'menus' => $menus,
            ]);
        }
        if(Schema::hasTable('pages')) {
            $pages = \App\Models\Page::where('published', true)->where('is_home', false)->get();

            if ($pages) {
                View::share('pages', $pages);
            }
        }
        
        Inertia::share([
            'auth' => function () {
                return [
                    'user' => Auth::user() ? Auth::user()->only(['id', 'name', 'email', 'is_admin', 'is_teacher', 'is_parent']) : null,
                    'logged_in_as' => Session::get('logged_in_as'), // Pass logged-in role
                ];
            },
        ]);
    }
}
