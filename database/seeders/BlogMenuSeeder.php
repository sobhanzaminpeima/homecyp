<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Post;
use App\Models\PostTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogMenuSeeder extends Seeder
{
    public function run(): void
    {
        // Footer menu items
        $col1 = [
            ['label' => 'New Projects', 'url' => '/projects'],
            ['label' => 'Resale Properties', 'url' => '/resale'],
            ['label' => 'Daily Rentals', 'url' => '/daily-rentals'],
            ['label' => 'Long-Term Rentals', 'url' => '/long-term-rentals'],
            ['label' => 'Investment Guide', 'url' => '/investment-guide'],
        ];
        $col2 = [
            ['label' => 'About Us', 'url' => '/about'],
            ['label' => 'Our Agents', 'url' => '/agents'],
            ['label' => 'Blog', 'url' => '/blog'],
            ['label' => 'Contact', 'url' => '/contact'],
            ['label' => 'FAQ', 'url' => '/faq'],
            ['label' => 'Privacy Policy', 'url' => '/privacy-policy'],
        ];
        foreach ($col1 as $i => $item) {
            MenuItem::firstOrCreate(['location' => 'footer_col1', 'label' => $item['label']], ['url' => $item['url'], 'sort_order' => $i]);
        }
        foreach ($col2 as $i => $item) {
            MenuItem::firstOrCreate(['location' => 'footer_col2', 'label' => $item['label']], ['url' => $item['url'], 'sort_order' => $i]);
        }

        // Sample blog posts
        $posts = [
            ['cat' => 'guide', 'title' => 'A Complete Guide to Buying Property in North Cyprus', 'excerpt' => 'Everything foreign buyers need to know about purchasing real estate in North Cyprus — from title deeds to taxes.', 'body' => '<p>North Cyprus has become one of the Mediterranean\'s most attractive property markets. In this guide we walk through the entire purchase process for foreign buyers.</p><h2>Title Deeds</h2><p>Understanding the different types of title deeds (Koçan) is essential before you buy.</p><h2>Taxes & Fees</h2><p>Property transfer tax, VAT and stamp duty remain among the lowest in the region.</p>'],
            ['cat' => 'investment', 'title' => 'Why North Cyprus Offers Some of the Highest Rental Yields', 'excerpt' => 'With 8-12% annual yields and growing tourism, North Cyprus is a standout for property investors.', 'body' => '<p>Rental yields in North Cyprus consistently outperform most European destinations.</p><p>Daily rentals near Long Beach and Kyrenia can achieve exceptional returns during peak season.</p>'],
            ['cat' => 'news', 'title' => 'Top 5 Areas to Invest in North Cyprus in 2026', 'excerpt' => 'From İskele Long Beach to Esentepe, discover the regions delivering the best capital growth.', 'body' => '<p>Location is everything. Here are the five regions our analysts are watching this year.</p><ol><li>İskele Long Beach</li><li>Esentepe</li><li>Kyrenia</li><li>Bahçeli</li><li>Famagusta</li></ol>'],
        ];
        foreach ($posts as $i => $p) {
            $post = Post::firstOrCreate(
                ['slug' => Str::slug($p['title'])],
                ['category' => $p['cat'], 'is_published' => true, 'is_featured' => $i === 0, 'published_at' => now()->subDays($i * 3)]
            );
            PostTranslation::updateOrCreate(
                ['post_id' => $post->id, 'locale' => 'en'],
                ['title' => $p['title'], 'excerpt' => $p['excerpt'], 'body' => $p['body'],
                 'meta_title' => $p['title'] . ' | HomeCyp', 'meta_description' => $p['excerpt']]
            );
        }
    }
}
