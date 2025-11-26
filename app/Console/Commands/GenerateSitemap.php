<?php

namespace App\Console\Commands;

use App\Models\Website\Blog;
use App\Models\Website\Career;
use App\Models\Website\Page;
use Illuminate\Console\Command;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sitemap\Sitemap;
use Carbon\Carbon;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-sitemap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap for the website';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $sitemap = Sitemap::create();
        
        // 1. Homepage
        $sitemap->add(Url::create('/')
            ->setPriority(1.0)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
        );
        
        // 2. Static pages (from Pages model)
        Page::where('published', true)->get()->each(function ($page) use ($sitemap) {
            $sitemap->add(
                Url::create(route('page.show', $page->slug))
                    ->setLastModificationDate($page->updated_at ?? now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.8)
            );
        });
        
        // 3. Blogs
        Blog::where('active', true)->get()->each(function ($blog) use ($sitemap) {
            $sitemap->add(
                Url::create(route('blogs.show', $blog->slug))
                    ->setLastModificationDate($blog->updated_at ?? now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.7)
            );
        });
        
        // 4. Careers
        Career::where('active', true)->get()->each(function ($career) use ($sitemap) {
            $sitemap->add(
                Url::create(route('careers.show', $career->slug))
                    ->setLastModificationDate($career->updated_at ?? now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.6)
            );
        });
        
        $sitemap->writeToFile(public_path('sitemap.xml'));
        
        $this->info('Sitemap generated successfully.');
    }
}
