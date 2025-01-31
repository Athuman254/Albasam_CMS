<?php

namespace App\Providers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class PageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(Request $request): void
    {
        View::composer('website.template-1.layouts.shared.header', function ($view) use ($request) {
            // Get the current URL slug
            $slug = ltrim($request->path(), '/');

            // Fetch the page based on the slug
            $page = Page::where('slug', $slug)
                ->with(['sections' => function ($query) {
                    $query->whereNull('parent_id')->where('is_active', true)->orderBy('order')
                        ->with(['children' => function ($subQuery) {
                            $subQuery->where('is_active', true)->orderBy('order');
                        }]);
                }])
                ->first();

            // Share the page data with the header
            $view->with('page', $page);
        });
    }
}
