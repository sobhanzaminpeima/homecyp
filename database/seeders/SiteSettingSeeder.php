<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'HomeCyp', 'group' => 'general'],
            ['key' => 'site_tagline', 'value' => 'Luxury Real Estate & Daily Rentals in North Cyprus', 'group' => 'general'],
            ['key' => 'site_email', 'value' => 'info@homecyp.com', 'group' => 'contact'],
            ['key' => 'site_phone', 'value' => '+90 533 845 64 97', 'group' => 'contact'],
            ['key' => 'site_whatsapp', 'value' => '905338456497', 'group' => 'contact'],
            ['key' => 'site_address', 'value' => 'North Cyprus, TRNC', 'group' => 'contact'],
            ['key' => 'facebook_url', 'value' => '', 'group' => 'social'],
            ['key' => 'instagram_url', 'value' => '', 'group' => 'social'],
            ['key' => 'google_maps_embed', 'value' => '', 'group' => 'contact'],
            ['key' => 'show_airbnb', 'value' => '1', 'group' => 'homepage'],
            ['key' => 'stat_happy_clients', 'value' => '500+', 'group' => 'stats'],
            ['key' => 'stat_years_experience', 'value' => '10+', 'group' => 'stats'],
            ['key' => 'google_analytics_id', 'value' => '', 'group' => 'seo'],
            ['key' => 'meta_pixel_id', 'value' => '', 'group' => 'seo'],
            ['key' => 'recaptcha_site_key', 'value' => '', 'group' => 'security'],
            ['key' => 'recaptcha_secret_key', 'value' => '', 'group' => 'security'],
        ];

        foreach ($settings as $setting) {
            SiteSetting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
