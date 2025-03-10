<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
            $pages = \App\Models\Page::where('is_published', true)->get();

            if ($pages) {
                View::share('pages', $pages);
            }
        }
    }
}
