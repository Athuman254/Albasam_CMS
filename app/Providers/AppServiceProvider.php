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
                View::share('institution', $institution);
            }
        }
        if(Schema::hasTable('pages')) {
            $pages = \App\Models\Page::where('is_published', true)->where('is_home', false)->get();

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
