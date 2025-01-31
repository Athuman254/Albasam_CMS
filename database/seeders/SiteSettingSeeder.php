<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SiteSetting::create([
            'template_name' => 'Default Template',
            'color' => '#ffffff',
            'secondary_color' => '#f0f0f0',
            'font_family' => 'Arial, sans-serif',
            'background_image' => null,
            'banner_text' => 'Welcome to Our Website!',
            'show_banner' => true,
            'meta_title' => 'Home - My Website',
            'meta_description' => 'This is the homepage of My Website.',
            'meta_keywords' => 'homepage, website, default',
            'is_active' => true,
        ]);
    }
}
