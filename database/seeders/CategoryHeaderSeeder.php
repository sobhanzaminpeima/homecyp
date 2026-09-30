<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class CategoryHeaderSeeder extends Seeder
{
    public function run(): void
    {
        // Blog categories
        $cats = [
            ['name' => 'News', 'name_tr' => 'Haberler', 'slug' => 'news', 'color' => '#3B82F6'],
            ['name' => 'Guide', 'name_tr' => 'Rehber', 'slug' => 'guide', 'color' => '#C9A84C'],
            ['name' => 'Investment', 'name_tr' => 'Yatırım', 'slug' => 'investment', 'color' => '#10B981'],
            ['name' => 'Lifestyle', 'name_tr' => 'Yaşam', 'slug' => 'lifestyle', 'color' => '#EC4899'],
        ];
        foreach ($cats as $i => $c) {
            Category::firstOrCreate(['slug' => $c['slug']], $c + ['type' => 'blog', 'sort_order' => $i]);
        }

        // Header menu items
        $header = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Projects', 'url' => '/projects'],
            ['label' => 'Resale', 'url' => '/resale'],
            ['label' => 'Invest', 'url' => '/investment-guide'],
            ['label' => 'Blog', 'url' => '/blog'],
            ['label' => 'About', 'url' => '/about'],
            ['label' => 'Contact', 'url' => '/contact'],
        ];
        foreach ($header as $i => $item) {
            MenuItem::firstOrCreate(
                ['location' => 'header', 'label' => $item['label']],
                ['url' => $item['url'], 'sort_order' => $i]
            );
        }
    }
}
