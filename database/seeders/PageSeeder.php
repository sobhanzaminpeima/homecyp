<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            'about' => [
                'en' => ['title' => 'About HomeCyp', 'content' => '<p>HomeCyp is a premium real estate platform specializing in luxury properties, investment opportunities, and daily rentals across North Cyprus. With years of experience in the local market, we help international and local clients find their perfect property.</p><p>Our mission is to make North Cyprus property investment simple, transparent, and rewarding for everyone.</p>'],
                'tr' => ['title' => 'HomeCyp Hakkında', 'content' => '<p>HomeCyp, Kuzey Kıbrıs genelinde lüks mülkler, yatırım fırsatları ve günlük kiralıklar konusunda uzmanlaşmış premium bir gayrimenkul platformudur.</p>'],
            ],
            'contact' => [
                'en' => ['title' => 'Contact Us', 'content' => '<p>Get in touch with our expert team. We are here to help you find your dream property in North Cyprus.</p>'],
                'tr' => ['title' => 'İletişim', 'content' => '<p>Uzman ekibimizle iletişime geçin. Kuzey Kıbrıs\'ta hayalinizdeki mülkü bulmanıza yardımcı olmak için buradayız.</p>'],
            ],
        ];

        foreach ($pages as $slug => $data) {
            $page = Page::firstOrCreate(['slug' => $slug], ['is_active' => true]);
            foreach (['en', 'tr'] as $locale) {
                PageTranslation::updateOrCreate(
                    ['page_id' => $page->id, 'locale' => $locale],
                    ['title' => $data[$locale]['title'], 'content' => $data[$locale]['content']]
                );
            }
        }
    }
}
